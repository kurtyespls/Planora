<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Location;
use App\Services\PlanoraService;
use Illuminate\Support\Facades\Log;
use Closure;

class PlanoraController extends Controller
{
    private PlanoraService $planoraService;

    public function __construct(PlanoraService $planoraService)
    {
        $this->planoraService = $planoraService;
    }

    // Valid rest-schedule values live in PlanoraService::REST_OPTIONS so that
    // validation and itinerary generation can never drift apart.

    /**
     * Role-aware entry point para sa bare domain: ang bisita ay nakakakita ng
     * marketing page, ang naka-sign in na tourist ay dumadaan sa planner, at
     * ang admin ay direktang pinapasok sa control center.
     *
     * Ang `/planora` ay sineserbisyuhan ng index() — hindi ng method na ito —
     * dahil doon naka-point ang "View Live App" (sidebar) at "Visit App"
     * (topbar) ng admin panel. Dapat buksan nila ang public app kahit may
     * aktibong admin session; dati ay bumabalik lang sila sa /admin/hotels.
     */
    public function home()
    {
        if (auth()->check() && auth()->user()->role === 'admin') {
            return redirect('/admin/hotels');
        }

        return $this->index();
    }

    /**
     * Ang public app: planner para sa naka-sign in, landing page para sa bisita.
     */
    public function index()
    {
        if (auth()->check()) {
            return view('planora', [
                // Exposed so the client-side estimate mirrors the server-side
                // budget rule instead of re-deriving it (see PlanoraService).
                'nightsOffset' => PlanoraService::NIGHTS_PER_DAY_OFFSET,
                'aiEnabled' => (bool) config('services.groq.key'),
                // Preset rest windows: isang source of truth para sa UI at sa
                // validation sa server (ang oras ay pwedeng i-edit ng user).
                'restWindows' => PlanoraService::REST_WINDOWS,
                // 12-hour na display ng parehong presets para sa UI chips; ang
                // data-start/data-end ay nananatiling 'HH:MM'.
                'restWindowDisplay' => PlanoraService::restWindowDisplay(),
            ]);
        }
        return view('welcome');
    }

    public function getHotels()
    {
        try {
            $hotels = $this->planoraService->getHotels();
            return response()->json($hotels);
        } catch (\Exception $e) {
            Log::error('Error fetching hotels', [
                'message' => $e->getMessage(),
                'user_id' => auth()->id(),
                'endpoint' => 'getHotels',
            ]);
            return response()->json(['error' => 'Failed to load hotels.'], 500);
        }
    }

    public function getNearbyPlaces(Request $request)
    {
        $lat = (float) $request->query('lat', 16.0438);
        $lon = (float) $request->query('lon', 120.3331);

        try {
            $places = $this->planoraService->getNearbyPlaces($lat, $lon);
            return response()->json($places);
        } catch (\Exception $e) {
            Log::error('Error fetching nearby places', [
                'message' => $e->getMessage(),
                'user_id' => auth()->id(),
                'endpoint' => 'getNearbyPlaces',
                'lat' => $lat,
                'lon' => $lon,
            ]);
            return response()->json(['error' => 'Failed to fetch nearby places.'], 500);
        }
    }

    /**
     * Curated Dagupan points of interest from the local `locations` table.
     *
     * Replaces the previous stub that always returned an empty array, which
     * made every check-in attempt fail with "Spot not found".
     */
    public function getTouristSpots(Request $request)
    {
        $term = trim((string) $request->query('q', ''));
        $category = trim((string) $request->query('category', ''));
        $categories = [
            Location::CATEGORY_RESTAURANT,
            Location::CATEGORY_MALL,
            Location::CATEGORY_BEACH,
            Location::CATEGORY_TOURIST,
        ];

        try {
            $spots = Location::query()
                ->when(
                    in_array($category, $categories, true),
                    fn ($query) => $query->ofCategory($category)
                )
                ->when(
                    $term !== '',
                    fn ($query) => $query->where('name', 'like', '%' . $term . '%')
                )
                ->orderBy('name')
                ->limit(20)
                ->get(['id', 'name', 'category', 'latitude', 'longitude']);

            return response()->json($spots->map(fn (Location $spot) => [
                'id'       => $spot->id,
                'name'     => $spot->name,
                'category' => $spot->category,
                'lat'      => $spot->latitude,
                'lon'      => $spot->longitude,
            ]));
        } catch (\Exception $e) {
            Log::error('Error fetching tourist spots', [
                'message' => $e->getMessage(),
                'user_id' => auth()->id(),
                'endpoint' => 'getTouristSpots',
                'q' => $term,
            ]);
            return response()->json(['error' => 'Failed to load tourist spots.'], 500);
        }
    }

    public function getWeather(Request $request)
    {
        $lat = (float) $request->query('lat', 16.0438);
        $lon = (float) $request->query('lon', 120.3331);

        $apiKey = config('services.openweather.key');
        if (!$apiKey) {
            Log::warning('Weather service not configured', [
                'user_id' => auth()->id(),
                'endpoint' => 'getWeather',
            ]);
            return response()->json(['error' => 'Weather service not configured.'], 503);
        }

        try {
            $data = $this->planoraService->getWeather($lat, $lon, $apiKey);

            if (empty($data)) {
                return response()->json(['error' => 'Weather data temporarily unavailable.'], 502);
            }

            return response()->json([
                'main'        => $data['weather'][0]['main'] ?? null,
                'description' => $data['weather'][0]['description'] ?? null,
                'feels_like'  => $data['main']['feels_like'] ?? null,
                'temp'        => $data['main']['temp'] ?? null,
                'humidity'    => $data['main']['humidity'] ?? null,
                'wind_speed'  => $data['wind']['speed'] ?? null,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching weather', [
                'message' => $e->getMessage(),
                'user_id' => auth()->id(),
                'endpoint' => 'getWeather',
            ]);
            return response()->json(['error' => 'Failed to fetch weather.'], 500);
        }
    }

    public function generatePlan(Request $request)
    {
        $validated = $request->validate([
            'hotel'          => 'required|string|max:255',
            'budget'         => 'required|numeric|min:1',
            'days'           => 'required|integer|min:1|max:30',
            'rest_days'      => 'nullable|array|max:4',
            // Tumatanggap ng preset label ('Morning') O custom na oras na
            // pinili ng traveller ('14:00-16:00'). Ang `in:` at `regex:` ay
            // hindi maaaring pagsamahin sa isang rule array (AND ang ibig
            // sabihin), kaya closure ang ginagamit para sa "alinman sa".
            'rest_days.*'    => [
                'string',
                'max:20',
                function (string $attribute, mixed $value, Closure $fail) {
                    if (!PlanoraService::isValidRestEntry((string) $value)) {
                        $fail('Each rest entry must be a preset like "Morning" or a time window like "14:00-16:00".');
                    }
                },
            ],
            'weather_desc'   => 'nullable|string|max:255',
            'nearby_places'  => 'nullable|string',
        ]);

        try {
            $result = $this->planoraService->generatePlan(
                $validated,
                $validated['weather_desc'] ?? null,
                $validated['nearby_places'] ?? null
            );

            if (isset($result['error'])) {
                return response()->json(['error' => $result['error']], 422);
            }

            return response()->json([
                'recommendation' => $result['recommendation'],
                'budget_warning' => $result['budget_warning'],
                'daily_allowance' => $result['daily_allowance'],
                'ai_provider' => $result['ai_provider'] ?? 'local',
            ]);
        } catch (\Exception $e) {
            Log::error('Critical Execution Failure in generatePlan', [
                'message' => $e->getMessage(),
                'user_id' => auth()->id(),
                'endpoint' => 'generatePlan',
                'hotel' => $validated['hotel'] ?? 'unknown',
                'days' => $validated['days'] ?? 0,
            ]);
            return response()->json([
                'recommendation' => "### System Alert\nAn unexpected processing exception occurred. Please try again."
            ], 500);
        }
    }
}