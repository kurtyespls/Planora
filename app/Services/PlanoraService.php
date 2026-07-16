<?php

namespace App\Services;

use App\Models\Hotel;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Business logic for Planora itinerary planning.
 * Extracted from PlanoraController to keep controllers thin.
 */
class PlanoraService
{
    /**
     * Canonical rest-schedule options. Single source of truth.
     */
    public const REST_OPTIONS = ['Morning', 'Afternoon', 'Night Shift', 'Whole Day'];

    /**
     * Fetch hotels with caching, returning paginated results.
     */
    public function getHotels(int $perPage = 50): array
    {
        return CacheService::getHotels(fn () => Hotel::paginate($perPage)->items());
    }

    /**
     * Fetch nearby places from Overpass API with caching.
     */
    public function getNearbyPlaces(float $lat, float $lon): array
    {
        return CacheService::getNearbyPlaces($lat, $lon, function () use ($lat, $lon) {
            return $this->fetchOverpassData($lat, $lon);
        });
    }

    /**
     * Fetch weather data from OpenWeatherMap with caching.
     */
    public function getWeather(float $lat, float $lon, string $apiKey): ?array
    {
        return CacheService::getWeather($lat, $lon, function () use ($lat, $lon, $apiKey) {
            return $this->fetchWeatherData($lat, $lon, $apiKey);
        });
    }

    /**
     * Generate an AI itinerary plan.
     */
    public function generatePlan(array $validated, ?string $weatherDesc, ?string $nearbyPlaces): array
    {
        $hotel = Hotel::whereRaw('LOWER(name) = ?', [strtolower(trim($validated['hotel']))])->first();

        $nightlyRate = 0;
        $totalHotelCost = 0;

        if ($hotel) {
            $nightlyRate = (float) preg_replace('/[^\d.]/', '', $hotel->price);
            $totalHotelCost = $nightlyRate * $validated['days'];

            if ($validated['budget'] < $totalHotelCost) {
                return [
                    'error' => "Insufficient Budget! The {$hotel->name} costs PHP " . number_format($nightlyRate) . " per night. For {$validated['days']} days, your hotel accommodation alone costs PHP " . number_format($totalHotelCost) . ". Please increase your budget or shorten your stay.",
                ];
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

        $plan = new Plan();
        $plan->user_id = auth()->id();
        $plan->hotel_name = strip_tags($validated['hotel']);
        $plan->budget = $validated['budget'];
        $plan->total_days = $days;
        $plan->rest_days = $restList;
        $plan->save();

        $poiList = $this->capNearbyPlaces($nearbyPlaces ?? '', 10);

        $prompt = $this->buildAiPrompt($validated, $nightlyRate, $dailyAllowance, $foodBudgetPerDay, $activityBudgetPerDay, $weatherDesc, $poiList, $restInstruction);

        $recommendation = null;

        if (env('GROQ_API_KEY')) {
            $recommendation = $this->callGroqApi($prompt);
        }

        if (empty($recommendation)) {
            $recommendation = $this->generateLocalFallbackItinerary(
                $validated, $restList, $wholeRestDayIndex,
                [
                    'nightlyRate' => $nightlyRate,
                    'foodBudgetPerDay' => $foodBudgetPerDay,
                    'activityBudgetPerDay' => $activityBudgetPerDay,
                ]
            );
        }

        $plan->ai_recommendation = $recommendation;
        $plan->save();

        return [
            'recommendation' => $recommendation,
            'budget_warning' => $budgetWarning,
            'daily_allowance' => round($dailyAllowance),
        ];
    }

    /**
     * Fetch data from Overpass API.
     */
    private function fetchOverpassData(float $lat, float $lon): array
    {
        $query = '[out:json][timeout:30];('
            . 'node["amenity"="restaurant"](around:2000,' . $lat . ',' . $lon . ');'
            . 'way["amenity"="restaurant"](around:2000,' . $lat . ',' . $lon . ');'
            . 'node["amenity"="fast_food"](around:2000,' . $lat . ',' . $lon . ');'
            . 'way["amenity"="fast_food"](around:2000,' . $lat . ',' . $lon . ');'
            . 'node["amenity"="cafe"](around:1500,' . $lat . ',' . $lon . ');'
            . 'way["amenity"="cafe"](around:1500,' . $lat . ',' . $lon . ');'
            . 'node["shop"="mall"](around:10000,' . $lat . ',' . $lon . ');'
            . 'way["shop"="mall"](around:10000,' . $lat . ',' . $lon . ');'
            . 'relation["shop"="mall"](around:10000,' . $lat . ',' . $lon . ');'
            . 'way["shop"="department_store"](around:10000,' . $lat . ',' . $lon . ');'
            . 'node["shop"="department_store"](around:10000,' . $lat . ',' . $lon . ');'
            . 'way["shop"="supermarket"](around:8000,' . $lat . ',' . $lon . ');'
            . 'node["shop"="supermarket"](around:8000,' . $lat . ',' . $lon . ');'
            . 'way["shop"="shopping_centre"](around:10000,' . $lat . ',' . $lon . ');'
            . 'relation["shop"="shopping_centre"](around:10000,' . $lat . ',' . $lon . ');'
            . 'way["shop"="shopping_center"](around:10000,' . $lat . ',' . $lon . ');'
            . 'node["tourism"~"attraction|viewpoint|museum|theme_park|zoo|aquarium|gallery|information"](around:8000,' . $lat . ',' . $lon . ');'
            . 'way["tourism"~"attraction|viewpoint|museum|theme_park|zoo|aquarium|gallery"](around:8000,' . $lat . ',' . $lon . ');'
            . 'relation["tourism"~"attraction|viewpoint|museum|theme_park|zoo|aquarium"](around:8000,' . $lat . ',' . $lon . ');'
            . 'node["historic"~"monument|memorial|castle|fort|ruins|archaeological_site"](around:8000,' . $lat . ',' . $lon . ');'
            . 'way["historic"~"monument|memorial|castle|fort|ruins|archaeological_site"](around:8000,' . $lat . ',' . $lon . ');'
            . 'node["natural"="beach"](around:10000,' . $lat . ',' . $lon . ');'
            . 'way["natural"="beach"](around:10000,' . $lat . ',' . $lon . ');'
            . 'node["leisure"="beach_resort"](around:10000,' . $lat . ',' . $lon . ');'
            . 'way["leisure"="beach_resort"](around:10000,' . $lat . ',' . $lon . ');'
            . 'node["tourism"="resort"](around:10000,' . $lat . ',' . $lon . ');'
            . 'way["tourism"="resort"](around:10000,' . $lat . ',' . $lon . ');'
            . 'node["leisure"="park"](around:5000,' . $lat . ',' . $lon . ');'
            . 'way["leisure"="park"](around:5000,' . $lat . ',' . $lon . ');'
            . ');out center 80;';

        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'User-Agent' => 'Planora/1.0 (Dagupan Itinerary App)',
                    'Accept'     => 'application/json',
                ])->get('https://overpass-api.de/api/interpreter', [
                    'data' => $query,
                ]);

            if (!$response->successful()) {
                Log::warning('Overpass API request failed', [
                    'status' => $response->status(),
                    'endpoint' => 'overpass-api.de/api/interpreter',
                ]);
                return [];
            }

            $data = $response->json();
            if (!isset($data['elements']) || !is_array($data['elements'])) {
                return [];
            }

            return $this->categorizePlaces($data['elements']);
        } catch (\Exception $e) {
            Log::error('Overpass API exception', [
                'message' => $e->getMessage(),
                'endpoint' => 'overpass-api.de/api/interpreter',
            ]);
            return [];
        }
    }

    /**
     * Categorize Overpass API elements into restaurant/mall/beach/tourist.
     */
    private function categorizePlaces(array $elements): array
    {
        $categorized = ['restaurant' => [], 'mall' => [], 'beach' => [], 'tourist' => []];
        $usedNames = ['restaurant' => [], 'mall' => [], 'beach' => [], 'tourist' => []];
        $limits = ['restaurant' => 6, 'mall' => 4, 'beach' => 4, 'tourist' => 6];

        foreach ($elements as $el) {
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
    }

    /**
     * Fetch weather data from OpenWeatherMap.
     */
    private function fetchWeatherData(float $lat, float $lon, string $apiKey): ?array
    {
        try {
            $response = Http::timeout(15)
                ->get('https://api.openweathermap.org/data/2.5/weather', [
                    'lat'    => $lat,
                    'lon'    => $lon,
                    'appid'  => $apiKey,
                    'units'  => 'metric',
                ]);

            if (!$response->successful()) {
                Log::warning('OpenWeatherMap API request failed', [
                    'status' => $response->status(),
                    'endpoint' => 'api.openweathermap.org/data/2.5/weather',
                ]);
                return null;
            }

            return $response->json();
        } catch (\Exception $e) {
            Log::error('OpenWeatherMap API exception', [
                'message' => $e->getMessage(),
                'endpoint' => 'api.openweathermap.org/data/2.5/weather',
            ]);
            return null;
        }
    }

    /**
     * Call Groq API for AI itinerary generation.
     */
    private function callGroqApi(string $prompt): ?string
    {
        try {
            $response = Http::timeout(20)
                ->retry(3, 500)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . env('GROQ_API_KEY'),
                    'Content-Type'  => 'application/json',
                ])->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => 'llama-3.1-8b-instant',
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are PLANORA, a precise, concise local travel planner for Dagupan City. You never pad your answers with filler text. You strictly obey location constraints.'],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => 0.2,
                ]);

            if ($response->successful()) {
                $aiData = $response->json();
                return $aiData['choices'][0]['message']['content'] ?? null;
            }

            Log::warning('Groq API request failed', [
                'status' => $response->status(),
                'endpoint' => 'api.groq.com/openai/v1/chat/completions',
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('Groq API exception', [
                'message' => $e->getMessage(),
                'endpoint' => 'api.groq.com/openai/v1/chat/completions',
            ]);
            return null;
        }
    }

    /**
     * Build the AI prompt for itinerary generation.
     */
    private function buildAiPrompt(array $data, float $nightlyRate, float $dailyAllowance, int $foodBudgetPerDay, int $activityBudgetPerDay, ?string $weatherDesc, string $poiList, string $restInstruction): string
    {
        return "You are PLANORA, an expert local travel guide for Dagupan City..."
            . " CRITICAL RULE: You MUST ONLY recommend places from this exact list: {$poiList}. DO NOT invent, hallucinate, or suggest ANY locations, restaurants, or spots that are not on this list. If the list is empty, focus your itinerary strictly on relaxing at the {$data['hotel']} amenities."
            . "\n\nEnsure you recommend malls if they are in the available points of interest."
            . "\n- When recommending a mall, list it with the label 'Mall' so the frontend can display the correct icon."
            . "\nEnsure you recommend beaches if they are in the available points of interest."
            . "\n- When recommending a beach, list it with the label 'Beach' so the frontend can display the correct icon."
            . "\n\nGenerate a CONCISE day-by-day itinerary. Follow these constraints exactly:"
            . "\n- Basecamp: {$data['hotel']} (PHP " . number_format($nightlyRate) . "/night)"
            . "\n- Trip Duration: {$data['days']} day(s)"
            . "\n- Remaining daily budget after hotel: roughly PHP " . number_format($dailyAllowance) . " (about PHP " . number_format($foodBudgetPerDay) . " for food, PHP " . number_format($activityBudgetPerDay) . " for activities/transport)"
            . "\n- Current Conditions: {$weatherDesc}"
            . "\n- Available nearby places: {$poiList}"
            . "\n- Rest schedule: {$restInstruction}"
            . "\n\nFormatting rules — these matter:"
            . "\n- Use '### Day X' as the heading for each day (or '### Full Rest Day' for a designated rest day)."
            . "\n- Each day must have AT MOST 4 bullet points: one for morning, one for afternoon, one for evening/night, and optionally one cost-summary line. Do not add more than 4 bullets per day."
            . "\n- Keep bullets short — one line each, with an approximate time and an approximate cost in PHP."
            . "\n- No long paragraphs, no repeated disclaimers, no filler commentary between days."
            . "\n- End with a single short '### Trip Summary' section (3 lines max): total estimated cost, remaining buffer, and one practical tip.";
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