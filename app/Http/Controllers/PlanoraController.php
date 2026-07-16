<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Plan;
use App\Models\Hotel;
use App\Services\CacheService;
use Illuminate\Support\Facades\Log;

class PlanoraController extends Controller
{
    /**
     * Canonical rest-schedule options. Keeping this as a single source of
     * truth means the validation rule and the prompt/fallback logic can
     * never drift out of sync with what the frontend actually renders.
     */
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

    // 1. Kukunin ang mga hotels mula sa Database (with caching)
    public function getHotels()
    {
        try {
            $hotels = CacheService::getHotels(fn () => Hotel::all());
            return response()->json($hotels);
        } catch (\Exception $e) {
            Log::error('Error fetching hotels: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load hotels.'], 500);
        }
    }

    // ── Nearby Places (Overpass API proxy) ──
    public function getNearbyPlaces(Request $request)
    {
        $lat = $request->query('lat', 16.0438);
        $lon = $request->query('lon', 120.3331);

        try {
            $places = CacheService::getNearbyPlaces((float) $lat, (float) $lon, function () use ($lat, $lon) {
                $query = '[out:json][timeout:30];(
                    node["amenity"="restaurant"](around:2000,' . $lat . ',' . $lon . ');
                    way["amenity"="restaurant"](around:2000,' . $lat . ',' . $lon . ');
                    node["amenity"="fast_food"](around:2000,' . $lat . ',' . $lon . ');
                    way["amenity"="fast_food"](around:2000,' . $lat . ',' . $lon . ');
                    node["amenity"="cafe"](around:1500,' . $lat . ',' . $lon . ');
                    way["amenity"="cafe"](around:1500,' . $lat . ',' . $lon . ');
                    node["shop"="mall"](around:10000,' . $lat . ',' . $lon . ');
                    way["shop"="mall"](around:10000,' . $lat . ',' . $lon . ');
                    relation["shop"="mall"](around:10000,' . $lat . ',' . $lon . ');
                    way["shop"="department_store"](around:10000,' . $lat . ',' . $lon . ');
                    node["shop"="department_store"](around:10000,' . $lat . ',' . $lon . ');
                    way["shop"="supermarket"](around:8000,' . $lat . ',' . $lon . ');
                    node["shop"="supermarket"](around:8000,' . $lat . ',' . $lon . ');
                    way["shop"="shopping_centre"](around:10000,' . $lat . ',' . $lon . ');
                    relation["shop"="shopping_centre"](around:10000,' . $lat . ',' . $lon . ');
                    way["shop"="shopping_center"](around:10000,' . $lat . ',' . $lon . ');
                    node["tourism"~"attraction|viewpoint|museum|theme_park|zoo|aquarium|gallery|information"](around:8000,' . $lat . ',' . $lon . ');
                    way["tourism"~"attraction|viewpoint|museum|theme_park|zoo|aquarium|gallery"](around:8000,' . $lat . ',' . $lon . ');
                    relation["tourism"~"attraction|viewpoint|museum|theme_park|zoo|aquarium"](around:8000,' . $lat . ',' . $lon . ');
                    node["historic"~"monument|memorial|castle|fort|ruins|archaeological_site"](around:8000,' . $lat . ',' . $lon . ');
                    way["historic"~"monument|memorial|castle|fort|ruins|archaeological_site"](around:8000,' . $lat . ',' . $lon . ');
                    node["natural"="beach"](around:10000,' . $lat . ',' . $lon . ');
                    way["natural"="beach"](around:10000,' . $lat . ',' . $lon . ');
                    node["leisure"="beach_resort"](around:10000,' . $lat . ',' . $lon . ');
                    way["leisure"="beach_resort"](around:10000,' . $lat . ',' . $lon . ');
                    node["tourism"="resort"](around:10000,' . $lat . ',' . $lon . ');
                    way["tourism"="resort"](around:10000,' . $lat . ',' . $lon . ');
                    node["leisure"="park"](around:5000,' . $lat . ',' . $lon . ');
                    way["leisure"="park"](around:5000,' . $lat . ',' . $lon . ');
                );out center 80;';

                $response = Http::withoutVerifying()
                    ->timeout(30)
                    ->withHeaders([
                        'User-Agent' => 'Planora/1.0 (Dagupan Itinerary App)',
                        'Accept'     => 'application/json',
                    ])->get('https://overpass-api.de/api/interpreter', [
                        'data' => $query,
                    ]);

                if (!$response->successful()) {
                    Log::warning('Overpass API returned status ' . $response->status() . ': ' . $response->body());
                    return [];
                }

                $data = $response->json();
                if (!isset($data['elements']) || !is_array($data['elements'])) {
                    return [];
                }

                $categorized = ['restaurant' => [], 'mall' => [], 'beach' => [], 'tourist' => []];
                $usedNames = ['restaurant' => [], 'mall' => [], 'beach' => [], 'tourist' => []];
                $limits = ['restaurant' => 6, 'mall' => 4, 'beach' => 4, 'tourist' => 6];

                foreach ($data['elements'] as $el) {
                    $name = $el['tags']['name'] ?? null;
                    if (!$name) continue;
                    $coords = $el['type'] === 'node' ? ['lat' => $el['lat'], 'lon' => $el['lon']] : ($el['center'] ?? null);
                    if (!$coords) continue;

                    $amenity = $el['tags']['amenity'] ?? null;
                    $shop = $el['tags']['shop'] ?? null;
                    $tourism = $el['tags']['tourism'] ?? null;
                    $leisure = $el['tags']['leisure'] ?? null;
                    $natural = $el['tags']['natural'] ?? null;
                    $historic = $el['tags']['historic'] ?? null;

                    if (in_array($amenity, ['restaurant', 'fast_food', 'cafe'])) {
                        $type = 'restaurant';
                    } elseif (in_array($shop, ['mall', 'department_store', 'supermarket', 'shopping_centre', 'shopping_center'])) {
                        $type = 'mall';
                    } elseif ($natural === 'beach' || $leisure === 'beach_resort' || $tourism === 'resort') {
                        $type = 'beach';
                    } elseif ($tourism || $historic || $leisure === 'park') {
                        $type = 'tourist';
                    } else {
                        continue;
                    }

                    $nameLower = strtolower(trim($name));
                    if (isset($usedNames[$type][$nameLower])) continue;
                    $usedNames[$type][$nameLower] = true;

                    if (count($categorized[$type]) < $limits[$type]) {
                        $categorized[$type][] = [
                            'name' => $name,
                            'lat'  => (float) $coords['lat'],
                            'lon'  => (float) $coords['lon'],
                        ];
                    }
                }

                return $categorized;
            });

            return response()->json($places);

        } catch (\Exception $e) {
            Log::error('Error fetching nearby places: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch nearby places.'], 500);
        }
    }

    // ── Live Weather (OpenWeatherMap proxy, server-side + cached) ──
    public function getWeather(Request $request)
    {
        $lat = (float) $request->query('lat', 16.0438);
        $lon = (float) $request->query('lon', 120.3331);

        $apiKey = env('OPENWEATHER_API_KEY');
        if (!$apiKey) {
            return response()->json(['error' => 'Weather service not configured.'], 503);
        }

        try {
            $data = CacheService::getWeather($lat, $lon, function () use ($lat, $lon, $apiKey) {
                $response = Http::withoutVerifying()
                    ->timeout(15)
                    ->get('https://api.openweathermap.org/data/2.5/weather', [
                        'lat'    => $lat,
                        'lon'    => $lon,
                        'appid'  => $apiKey,
                        'units'  => 'metric',
                    ]);

                if (!$response->successful()) {
                    Log::warning('OpenWeatherMap returned status ' . $response->status() . ': ' . $response->body());
                    return null;
                }

                return $response->json();
            });

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
            Log::error('Error fetching weather: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch weather.'], 500);
        }
    }

    // 2. Logic para sa pag-generate ng AI Plan na may Budget Check
    public function generatePlan(Request $request)
    {
        $validated = $request->validate([
            'hotel'          => 'required|string|max:255',
            'budget'         => 'required|numeric|min:1',
            'days'           => 'required|integer|min:1|max:30',
            'rest_days'      => 'nullable|array',
            'rest_days.*'    => 'string|in:' . implode(',', self::REST_OPTIONS),
            'weather_desc'   => 'nullable|string|max:255',
            'nearby_places'  => 'nullable|string'
        ]);

        $hotel = Hotel::whereRaw('LOWER(name) = ?', [strtolower(trim($validated['hotel']))])->first();

        $nightlyRate = 0;
        $totalHotelCost = 0;

        if ($hotel) {
            $nightlyRate = (float) preg_replace('/[^\d.]/', '', $hotel->price);
            $totalHotelCost = $nightlyRate * $validated['days'];

            if ($validated['budget'] < $totalHotelCost) {
                return response()->json([
                    'error' => "Insufficient Budget! The {$hotel->name} costs PHP " . number_format($nightlyRate) . " per night. For {$validated['days']} days, your hotel accommodation alone costs PHP " . number_format($totalHotelCost) . ". Please increase your budget or shorten your stay."
                ], 422);
            }
        }

        $remainingBudget = max(0, $validated['budget'] - $totalHotelCost);
        $days = $validated['days'];
        $dailyAllowance = $days > 0 ? $remainingBudget / $days : 0;
        $foodBudgetPerDay = round($dailyAllowance * 0.6);
        $activityBudgetPerDay = round($dailyAllowance * 0.4);

        $budgetWarning = null;
        if ($hotel && $dailyAllowance > 0 && $dailyAllowance < 500) {
            $budgetWarning = "Heads up: after accommodation, you have roughly PHP " . number_format($dailyAllowance) . " per day left for food and activities. That's tight — consider budgeting an extra PHP " . number_format((500 - $dailyAllowance) * $days) . " overall for a more comfortable trip.";
        }

        $restList = $validated['rest_days'] ?? [];
        $wholeRestDayIndex = $this->resolveWholeRestDay($restList, $days);
        $restInstruction = $this->buildRestInstruction($restList, $wholeRestDayIndex, $days);

        try {
            $plan = new Plan();
            $plan->user_id = auth()->id();
            $plan->hotel_name = strip_tags($validated['hotel']);
            $plan->budget = $validated['budget'];
            $plan->total_days = $days;
            $plan->rest_days = $restList;
            $plan->save();

            $poiList = $this->capNearbyPlaces($validated['nearby_places'] ?? '', 10);

            $prompt = "You are PLANORA, an expert local travel guide for Dagupan City...
CRITICAL RULE: You MUST ONLY recommend places from this exact list: {$poiList}. DO NOT invent, hallucinate, or suggest ANY locations, restaurants, or spots that are not on this list. If the list is empty, focus your itinerary strictly on relaxing at the {$validated['hotel']} amenities.

Ensure you recommend malls if they are in the available points of interest.
- When recommending a mall, list it with the label 'Mall' so the frontend can display the correct icon.
Ensure you recommend beaches if they are in the available points of interest.
- When recommending a beach, list it with the label 'Beach' so the frontend can display the correct icon.

Generate a CONCISE day-by-day itinerary. Follow these constraints exactly:
- Basecamp: {$validated['hotel']} (PHP " . number_format($nightlyRate) . "/night)
- Trip Duration: {$days} day(s)
- Remaining daily budget after hotel: roughly PHP " . number_format($dailyAllowance) . " (about PHP " . number_format($foodBudgetPerDay) . " for food, PHP " . number_format($activityBudgetPerDay) . " for activities/transport)
- Current Conditions: {$validated['weather_desc']}
- Available nearby places: {$poiList}
- Rest schedule: {$restInstruction}

Formatting rules — these matter:
- Use '### Day X' as the heading for each day (or '### Full Rest Day' for a designated rest day).
- Each day must have AT MOST 4 bullet points: one for morning, one for afternoon, one for evening/night, and optionally one cost-summary line. Do not add more than 4 bullets per day.
- Keep bullets short — one line each, with an approximate time and an approximate cost in PHP.
- No long paragraphs, no repeated disclaimers, no filler commentary between days.
- End with a single short '### Trip Summary' section (3 lines max): total estimated cost, remaining buffer, and one practical tip.";

            $recommendation = null;

            if (env('GROQ_API_KEY')) {
                $response = Http::withoutVerifying()
                    ->timeout(20)
                    ->retry(3, 500)
                    ->withHeaders([
                        'Authorization' => 'Bearer ' . env('GROQ_API_KEY'),
                        'Content-Type'  => 'application/json',
                    ])->post('https://api.groq.com/openai/v1/chat/completions', [
                        'model' => 'llama-3.1-8b-instant',
                        'messages' => [
                            ['role' => 'system', 'content' => 'You are PLANORA, a precise, concise local travel planner for Dagupan City. You never pad your answers with filler text. You strictly obey location constraints.'],
                            ['role' => 'user', 'content' => $prompt]
                        ],
                        'temperature' => 0.2
                    ]);

                if ($response->successful()) {
                    $aiData = $response->json();
                    $recommendation = $aiData['choices'][0]['message']['content'] ?? null;
                } else {
                    Log::warning('Groq API failed: ' . $response->body());
                }
            }

            if (empty($recommendation)) {
                $recommendation = $this->generateLocalFallbackItinerary($validated, $restList, $wholeRestDayIndex, [
                    'nightlyRate' => $nightlyRate,
                    'foodBudgetPerDay' => $foodBudgetPerDay,
                    'activityBudgetPerDay' => $activityBudgetPerDay,
                ]);
            }

            $plan->ai_recommendation = $recommendation;
            $plan->save();

            return response()->json([
                'recommendation' => $recommendation,
                'budget_warning' => $budgetWarning,
                'daily_allowance' => round($dailyAllowance),
            ]);

        } catch (\Exception $e) {
            Log::error('Critical Execution Failure: ' . $e->getMessage());
            return response()->json([
                'recommendation' => "### System Alert\nAn unexpected processing exception occurred. Please try again."
            ], 500);
        }
    }

    private function capNearbyPlaces(string $raw, int $limit): string
    {
        if (trim($raw) === '') {
            return 'No nearby places found via online maps. Focus purely on hotel amenities.';
        }
        $items = array_filter(array_map('trim', explode('|', $raw)));
        $items = array_slice($items, 0, $limit);
        return implode(', ', $items);
    }

    private function resolveWholeRestDay(array $restList, int $days): ?int
    {
        if (!in_array('Whole Day', $restList) || $days < 2) {
            return null;
        }
        return (int) ceil($days / 2);
    }

    private function buildRestInstruction(array $restList, ?int $wholeRestDayIndex, int $days): string
    {
        if (empty($restList)) {
            return 'No specific rest preference — plan a full, active day each day.';
        }
        $parts = [];
        if (in_array('Morning', $restList)) {
            $parts[] = 'keep mornings free/slow (no activities before ~11 AM)';
        }
        if (in_array('Afternoon', $restList)) {
            $parts[] = 'keep afternoons free/slow (light or no activities 12–5 PM)';
        }
        if (in_array('Night Shift', $restList)) {
            $parts[] = 'this traveler is a night owl — shift energetic activities to late evening/night and keep early mornings light';
        }
        if ($wholeRestDayIndex !== null) {
            $parts[] = "designate Day {$wholeRestDayIndex} as a full rest day with no scheduled activities";
        }
        return implode('; ', $parts) . '.';
    }

    private function generateLocalFallbackItinerary(array $data, array $restList, ?int $wholeRestDayIndex, array $budget): string
    {
        $hotel = $data['hotel'];
        $days = $data['days'];
        $totalBudget = $data['budget'];
        
        $pois = !empty($data['nearby_places'])
            ? array_filter(array_map('trim', explode('|', $data['nearby_places'])))
            : [];
        $pois = array_values($pois);
        $poiCount = count($pois);

        $restMorning = in_array('Morning', $restList);
        $restAfternoon = in_array('Afternoon', $restList);
        $nightOwl = in_array('Night Shift', $restList);

        $markdown = "### Trip Overview\n";
        $markdown .= "- **{$hotel}**, PHP " . number_format($budget['nightlyRate']) . "/night for {$days} day(s) — total budget PHP " . number_format($totalBudget) . ".\n";
        $markdown .= "- Daily allowance after hotel: ~PHP " . number_format($budget['foodBudgetPerDay'] + $budget['activityBudgetPerDay']) . " (food + activities).\n\n";

        $poiIndex = 0;

        for ($i = 1; $i <= $days; $i++) {
            if ($wholeRestDayIndex !== null && $i === $wholeRestDayIndex) {
                $markdown .= "### Full Rest Day (Day {$i})\n";
                $markdown .= "- No activities scheduled — relax at the hotel, spa, or pool.\n";
                $markdown .= "- Optional: light walk around the neighborhood in the evening.\n\n";
                continue;
            }

            $markdown .= "### Day {$i}\n";

            if ($restMorning) {
                $markdown .= "- **Morning (free):** Sleep in, breakfast at the hotel whenever you're ready.\n";
            } else {
                $markdown .= "- **8:00 AM:** Breakfast near {$hotel}, then head out (~PHP " . number_format($budget['foodBudgetPerDay'] * 0.3) . ").\n";
            }

            if ($restAfternoon) {
                $markdown .= "- **Afternoon (free):** Downtime back at the hotel — swim, rest, or nap.\n";
            } elseif ($poiCount > 0) {
                $markdown .= "- **10:30 AM:** Visit **{$pois[$poiIndex % $poiCount]}** (~PHP " . number_format($budget['activityBudgetPerDay'] * 0.6) . " entrance/transport).\n";
                $poiIndex++;
            } else {
                $markdown .= "- **10:30 AM:** Enjoy the amenities at {$hotel} since there are no nearby spots in our database.\n";
            }

            if ($nightOwl) {
                $spot = $poiCount > 0 ? $pois[$poiIndex % $poiCount] : 'near the hotel';
                $markdown .= "- **9:00 PM:** Late dinner and a night stroll around **{$spot}** (~PHP " . number_format($budget['foodBudgetPerDay'] * 0.7) . ").\n";
                $poiIndex++;
            } else {
                $markdown .= "- **6:00 PM:** Dinner near the hotel (~PHP " . number_format($budget['foodBudgetPerDay'] * 0.7) . ").\n";
            }

            $markdown .= "\n";
        }

        $markdown .= "### Trip Summary\n";
        $markdown .= "- Estimated total spend stays within PHP " . number_format($totalBudget) . ".\n";
        $markdown .= "- Tip: confirm entrance fees and transport fares on-site, as prices can shift seasonally.\n";

        return $markdown;
    }
}