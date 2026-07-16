<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PlanoraService;
use App\Services\CacheService;
use Illuminate\Support\Facades\Log;

class PlanoraController extends Controller
{
    private PlanoraService $planoraService;

    public function __construct(PlanoraService $planoraService)
    {
        $this->planoraService = $planoraService;
    }

    private const REST_OPTIONS = ['Morning', 'Afternoon', 'Night Shift', 'Whole Day'];

    public function index()
    {
        if (auth()->check()) {
            if (auth()->user()->role === 'admin') {
                return redirect('/admin/hotels');
            }
            return view('planora');
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

    public function getWeather(Request $request)
    {
        $lat = (float) $request->query('lat', 16.0438);
        $lon = (float) $request->query('lon', 120.3331);

        $apiKey = env('OPENWEATHER_API_KEY');
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
            'rest_days'      => 'nullable|array',
            'rest_days.*'    => 'string|in:' . implode(',', self::REST_OPTIONS),
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