<?php

namespace App\Services;

use App\Models\Hotel;
use App\Models\Plan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Business logic for Planora itinerary planning.
 * Extracted from PlanoraController to keep controllers thin.
 */
class PlanoraService
{
    /**
     * Preset rest schedule → katumbas na oras. Single source of truth: ito ang
     * ipinapasa ng controller sa browser at ito rin ang ginagamit ng parsing sa
     * ibaba. Ang oras ay maaaring i-edit ng traveller sa UI, kaya ang window
     * (hindi ang label) ang aktwal na sinusunod ng itinerary.
     */
    public const REST_WINDOWS = [
        'Morning'   => ['08:00', '12:00'],
        'Afternoon' => ['13:00', '17:00'],
        'Evening'   => ['19:00', '23:00'],
        'Whole Day' => ['00:00', '23:59'],
    ];

    /**
     * Dating labels na tinatanggap pa rin para sa mga naunang naka-save na plan.
     * Ang 'Night Shift' ay chronotype noon ("night owl"); ngayon ay katumbas na
     * ito ng 'Evening' window dahil oras na ang kinukuha sa traveller.
     */
    public const REST_LEGACY_ALIASES = ['Night Shift' => 'Evening'];

    /**
     * Lahat ng tinatanggap na label: presets + legacy aliases.
     */
    public const REST_OPTIONS = ['Morning', 'Afternoon', 'Evening', 'Night Shift', 'Whole Day'];

    /**
     * Nights charged for an N-day trip: N days minus this offset. A single-day
     * trip needs no overnight stay, and every longer trip needs one night fewer
     * than its day count. Public so the browser can mirror the rule instead of
     * re-implementing the arithmetic (see planora.blade.php).
     */
    public const NIGHTS_PER_DAY_OFFSET = 1;

    /**
     * Format ng custom na rest window na ipinapadala ng browser.
     */
    private const REST_WINDOW_PATTERN = '/^([01]\d|2[0-3]):([0-5]\d)-([01]\d|2[0-3]):([0-5]\d)$/';

    public static function nightsFor(int $days): int
    {
        return max(0, $days - self::NIGHTS_PER_DAY_OFFSET);
    }

    /**
     * Tumatanggap ng preset label ('Morning') O custom na window
     * ('14:00-16:00') — ang traveller mismo ang pumipili ng oras ng rest.
     */
    public static function isValidRestEntry(string $value): bool
    {
        return in_array(trim($value), self::REST_OPTIONS, true)
            || self::parseRestWindow($value) !== null;
    }

    /**
     * '14:00-16:00' → ['start' => '14:00', 'end' => '16:00', 'overnight' => false, 'minutes' => 120].
     * Null kapag mali ang format. Ang end na mas maaga o katumbas ng start ay
     * itinuturing na overnight (hal. '22:00-06:00').
     *
     * @return array{start: string, end: string, overnight: bool, minutes: int}|null
     */
    public static function parseRestWindow(string $value): ?array
    {
        if (!preg_match(self::REST_WINDOW_PATTERN, trim($value), $matches)) {
            return null;
        }

        $start = ((int) $matches[1]) * 60 + ((int) $matches[2]);
        $end = ((int) $matches[3]) * 60 + ((int) $matches[4]);
        $overnight = $end <= $start;

        return [
            'start' => $matches[1] . ':' . $matches[2],
            'end' => $matches[3] . ':' . $matches[4],
            'overnight' => $overnight,
            'minutes' => $overnight ? (1440 - $start + $end) : ($end - $start),
        ];
    }

    /**
     * Isang canonical na listahan ng rest windows mula sa halo-halong labels
     * ('Morning') at custom ranges ('14:00-16:00').
     *
     * @param  array<int, mixed>  $restDays
     * @return array<int, array{start: string, end: string, overnight: bool, minutes: int, label: string}>
     */
    public static function normalizeRestSchedule(array $restDays): array
    {
        $windows = [];

        foreach ($restDays as $entry) {
            if (!is_string($entry) || trim($entry) === '') {
                continue;
            }

            $entry = trim($entry);
            $preset = self::REST_LEGACY_ALIASES[$entry] ?? $entry;

            if (isset(self::REST_WINDOWS[$preset])) {
                [$start, $end] = self::REST_WINDOWS[$preset];
                $window = self::parseRestWindow($start . '-' . $end);

                if ($window !== null) {
                    $windows[] = $window + ['label' => $preset];
                }
                continue;
            }

            $window = self::parseRestWindow($entry);

            if ($window === null) {
                continue;
            }

            // Ang buong araw (00:00-23:59) ay 'Whole Day' pa rin: doon naka-base
            // ang full-rest-day rule, kaya hindi ito dapat maging generic range.
            $window['label'] = ($window['start'] === '00:00' && $window['end'] === '23:59')
                ? 'Whole Day'
                : $entry;

            $windows[] = $window;
        }

        return $windows;
    }

    /**
     * Human-readable na label para sa isang naka-save na entry: legacy label
     * kung label, o "14:00–16:00 (2h)" kung window.
     */
    public static function restEntryLabel(string $entry): string
    {
        $entry = trim($entry);

        if (in_array($entry, self::REST_OPTIONS, true)) {
            return self::REST_LEGACY_ALIASES[$entry] ?? $entry;
        }

        $window = self::parseRestWindow($entry);

        if ($window === null) {
            return $entry;
        }

        $hours = $window['minutes'] / 60;
        $duration = $hours >= 1 ? round($hours, 1) . 'h' : $window['minutes'] . 'm';

        return $window['start'] . '–' . $window['end'] . ' (' . $duration . ')';
    }

    /**
     * Fetch hotels with caching, returning paginated results.
     */
    public function getHotels(int $perPage = 50): array
    {
        return CacheService::getHotels(fn () => Hotel::orderByPrice()->paginate($perPage)->items());
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
     * Generate an itinerary plan and persist it for the signed-in user.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException when guest.
     */
    public function generatePlan(array $validated, ?string $weatherDesc, ?string $nearbyPlaces): array
    {
        // A plan must always have an owner. Persisting one with a null user_id
        // makes it unreachable from every profile page.
        abort_unless(auth()->check(), 403, 'You must be signed in to generate a plan.');

        $result = $this->buildItinerary($validated, $weatherDesc, $nearbyPlaces);

        if (isset($result['error'])) {
            return $result;
        }

        // One atomic write: the plan row only comes into existence once the
        // itinerary text is ready, so a failure mid-generation can no longer
        // leave an ownerless, empty plan behind.
        DB::transaction(function () use ($validated, $result) {
            Plan::create([
                'user_id' => auth()->id(),
                'hotel_name' => strip_tags($validated['hotel']),
                'budget' => $validated['budget'],
                'total_days' => (int) $validated['days'],
                'rest_days' => $validated['rest_days'] ?? [],
                'ai_recommendation' => $result['recommendation'],
                'ai_provider' => $result['ai_provider'],
            ]);
        });

        return $result;
    }

    /**
     * Rebuild a saved plan's itinerary from its stored inputs, in place, so the
     * traveller can ask for a fresh version without losing the plan's identity.
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException when guest.
     */
    public function regeneratePlan(Plan $plan): array
    {
        abort_unless(auth()->check(), 403, 'You must be signed in to regenerate a plan.');

        $result = $this->buildItinerary([
            'hotel' => $plan->hotel_name,
            'budget' => (float) $plan->budget,
            'days' => (int) $plan->total_days,
            'rest_days' => $plan->rest_days ?? [],
        ], null, null, true);

        if (isset($result['error'])) {
            return $result;
        }

        DB::transaction(function () use ($plan, $result) {
            $plan->update([
                'ai_recommendation' => $result['recommendation'],
                'ai_provider' => $result['ai_provider'],
            ]);
        });

        return $result;
    }

    /**
     * Build the itinerary payload without touching the database, so creating a
     * plan and regenerating one share exactly one implementation.
     *
     * @param bool $resolvePlacesWhenMissing Look up points of interest from the hotel
     *        coordinates when the caller supplied none. Only worth doing for a
     *        user-initiated regeneration: in the browser flow the client has already
     *        done that lookup, and a second 30s Overpass call would be wasted work.
     *
     * @return array<string, mixed> Either ['error' => string] or the itinerary payload.
     */
    private function buildItinerary(array $validated, ?string $weatherDesc, ?string $nearbyPlaces, bool $resolvePlacesWhenMissing = false): array
    {
        $hotel = Hotel::whereRaw('LOWER(name) = ?', [strtolower(trim($validated['hotel']))])->first();

        // A plan must reference a real hotel: without one, accommodation would
        // silently cost nothing and the budget guard would never fire.
        if (!$hotel) {
            return [
                'error' => 'We could not find that stay in our Dagupan listings. Please pick one from the list and try again.',
            ];
        }

        $days = (int) $validated['days'];
        $nights = self::nightsFor($days);
        $nightlyRate = (float) $hotel->price;
        $totalHotelCost = $nightlyRate * $nights;

        if ((float) $validated['budget'] < $totalHotelCost) {
            return [
                'error' => "Insufficient Budget! The {$hotel->name} costs PHP " . number_format($nightlyRate) . " per night. "
                    . "For {$days} day(s) ({$nights} night(s)) your accommodation alone costs PHP " . number_format($totalHotelCost) . '. '
                    . 'Please increase your budget or shorten your stay.',
            ];
        }

        $remainingBudget = max(0, (float) $validated['budget'] - $totalHotelCost);
        $dailyAllowance = $days > 0 ? $remainingBudget / $days : 0;
        $foodBudgetPerDay = round($dailyAllowance * 0.6);
        $activityBudgetPerDay = round($dailyAllowance * 0.4);

        // Conditions are resolved here rather than trusted from the client, and
        // they also drive the practical advice attached to the budget warning.
        $weatherDesc = $weatherDesc ?: $this->resolveWeatherSummary($hotel);
        $budgetWarning = $this->buildBudgetWarning($hotel, $dailyAllowance, $days, $weatherDesc);

        $restList = $validated['rest_days'] ?? [];
        $wholeRestDayIndex = $this->resolveWholeRestDay($restList, $days);
        $restInstruction = $this->buildRestInstruction($restList, $wholeRestDayIndex, $days);

        // A regeneration arrives without a browser-supplied POI list, so the
        // places are looked up from the hotel coordinates instead.
        if ($resolvePlacesWhenMissing && trim((string) $nearbyPlaces) === '') {
            $nearbyPlaces = $this->describeNearbyPlaces($hotel);
        }

        $poiList = $this->capNearbyPlaces($nearbyPlaces, 10);

        $prompt = $this->buildAiPrompt(
            $validated,
            $hotel,
            $nightlyRate,
            $dailyAllowance,
            $foodBudgetPerDay,
            $activityBudgetPerDay,
            $weatherDesc,
            $poiList,
            $restInstruction
        );

        $apiKey = config('services.groq.key');
        $recommendation = $apiKey ? $this->callGroqApi($prompt, $apiKey) : null;
        $aiProvider = empty($recommendation) ? 'local' : 'groq';

        if (empty($recommendation)) {
            $recommendation = $this->generateLocalFallbackItinerary(
                $validated, $restList, $wholeRestDayIndex,
                [
                    'nightlyRate' => $nightlyRate,
                    'foodBudgetPerDay' => $foodBudgetPerDay,
                    'activityBudgetPerDay' => $activityBudgetPerDay,
                ],
                $nearbyPlaces
            );
        }

        return [
            'recommendation' => $recommendation,
            'budget_warning' => $budgetWarning,
            'daily_allowance' => round($dailyAllowance),
            'nights' => $nights,
            'ai_provider' => $aiProvider,
        ];
    }

    /**
     * Pre-trip advice shown above the itinerary: a tight-budget note and/or a
     * weather heads-up that points the traveller at indoor options.
     */
    private function buildBudgetWarning(?Hotel $hotel, float $dailyAllowance, int $days, ?string $weatherDesc): ?string
    {
        $notes = [];

        if ($hotel && $dailyAllowance > 0 && $dailyAllowance < 500) {
            $notes[] = 'Heads up: after accommodation, you have roughly PHP ' . number_format($dailyAllowance)
                . " per day left for food and activities. That's tight — consider budgeting an extra PHP "
                . number_format((500 - $dailyAllowance) * $days) . ' overall for a more comfortable trip.';
        }

        if ($this->isWetWeather($weatherDesc)) {
            $notes[] = 'Wet weather is expected (' . $weatherDesc . '). Line up one indoor fallback per day — '
                . 'a mall, a museum, or a cafe — and treat the outdoor stops as movable.';
        }

        return $notes === [] ? null : implode(' ', $notes);
    }

    /**
     * Conditions are summarised as "Main — description, temp…", so match on the
     * words the OpenWeatherMap condition strings actually use.
     */
    private function isWetWeather(?string $weatherDesc): bool
    {
        if (!$weatherDesc) {
            return false;
        }

        return (bool) preg_match('/\b(rain|drizzle|thunderstorm|shower|squall|storm)\b/i', $weatherDesc);
    }

    /**
     * Resolve points of interest from the hotel coordinates for generation that
     * is not driven by the browser (regenerating a saved plan).
     */
    private function describeNearbyPlaces(?Hotel $hotel): string
    {
        if (!$hotel || $hotel->lat === null || $hotel->lon === null) {
            return '';
        }

        $categorized = $this->getNearbyPlaces((float) $hotel->lat, (float) $hotel->lon);
        $labels = ['restaurant' => 'Restaurant', 'mall' => 'Mall', 'beach' => 'Beach', 'tourist' => 'Tourist Spot'];
        $names = [];

        foreach ($labels as $type => $label) {
            foreach ($categorized[$type] ?? [] as $place) {
                if (!empty($place['name'])) {
                    $names[] = $place['name'] . ' (' . $label . ')';
                }
            }
        }

        return implode('|', array_slice($names, 0, 20));
    }

    /**
     * Build a one-line weather summary for the AI prompt.
     *
     * Returns null when no key is configured or the stay has no coordinates, so
     * the prompt simply omits current conditions instead of asserting that
     * weather data is unavailable.
     */
    private function resolveWeatherSummary(?Hotel $hotel): ?string
    {
        $apiKey = config('services.openweather.key');

        if (!$apiKey || !$hotel || $hotel->lat === null || $hotel->lon === null) {
            Log::info('Weather summary skipped for itinerary prompt', [
                'hotel' => $hotel?->name,
                'weather_api_configured' => (bool) $apiKey,
            ]);
            return null;
        }

        $data = $this->getWeather((float) $hotel->lat, (float) $hotel->lon, $apiKey);

        if (empty($data['weather'][0]['main'])) {
            return null;
        }

        return sprintf(
            '%s — %s, %s°C (feels like %s°C), humidity %s%%, wind %s m/s',
            $data['weather'][0]['main'],
            $data['weather'][0]['description'] ?? 'n/a',
            $data['main']['temp'] ?? 'n/a',
            $data['main']['feels_like'] ?? 'n/a',
            $data['main']['humidity'] ?? 'n/a',
            $data['wind']['speed'] ?? 'n/a'
        );
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
    private function callGroqApi(string $prompt, string $apiKey): ?string
    {
        $model = config('services.groq.model', 'qwen/qwen3.8-27b');
        $timeout = (int) config('services.groq.timeout', 25);
        $temperature = (float) config('services.groq.temperature', 0.2);

        try {
            $response = Http::timeout($timeout)
                ->retry(3, 500)
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type'  => 'application/json',
                ])->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => $model,
                    'messages' => [
                        ['role' => 'system', 'content' => 'You are PLANORA, a precise, concise local travel planner for Dagupan City, Philippines. You never pad answers with filler. You strictly obey location constraints, rest windows, and budget limits.'],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'temperature' => $temperature,
                ]);

            if ($response->successful()) {
                $aiData = $response->json();
                $content = $aiData['choices'][0]['message']['content'] ?? null;
                return $this->sanitizeAiOutput($content);
            }

            Log::warning('Groq API request failed', [
                'status' => $response->status(),
                'model' => $model,
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
     * Clean markdown output from the AI: strip markdown code fences that
     * some models wrap around the response, and trim extra whitespace.
     */
    private function sanitizeAiOutput(?string $content): ?string
    {
        if ($content === null) {
            return null;
        }

        $cleaned = trim($content);
        $cleaned = preg_replace('/^```(?:markdown)?\s*\n/i', '', $cleaned);
        $cleaned = preg_replace('/\n```\s*$/', '', $cleaned);

        return trim($cleaned);
    }

    /**
     * Build the AI prompt for itinerary generation.
     */
    private function buildAiPrompt(
        array $data,
        ?Hotel $hotel,
        float $nightlyRate,
        float $dailyAllowance,
        int $foodBudgetPerDay,
        int $activityBudgetPerDay,
        ?string $weatherDesc,
        string $poiList,
        string $restInstruction
    ): string {
        $amenities = $hotel?->amenities ? trim($hotel->amenities) : '';
        $hotelAddress = $hotel?->address ? trim($hotel->address) : '';

        $hotelDetails = "Basecamp: {$data['hotel']} (PHP " . number_format($nightlyRate) . "/night)";
        if ($hotelAddress !== '') {
            $hotelDetails .= " located at {$hotelAddress}";
        }
        if ($amenities !== '') {
            $hotelDetails .= ". Amenities available at this stay: {$amenities}";
        }

        return "You are PLANORA, an expert local travel planner for Dagupan City, Pangasinan, Philippines."
            . "\n\nCRITICAL RULE #1 — ZERO HALLUCINATIONS: You MUST ONLY recommend places from this exact list: {$poiList}."
            . " DO NOT invent, hallucinate, or suggest ANY locations, restaurants, or spots that are not on this list."
            . " If the list is empty, focus your itinerary strictly on relaxing at {$data['hotel']} using its amenities: " . ($amenities ?: 'hotel facilities') . "."
            . "\n\nCRITICAL RULE #2 — NO REPEATING STOPS: Avoid repeating the same restaurant, mall, beach, or tourist spot across different days unless the list has fewer spots than trip days."
            . "\n\nCRITICAL RULE #3 — LOCAL CATEGORY LABELS:"
            . "\n- When recommending a mall, tag it with '(Mall)' so the frontend displays the correct icon."
            . "\n- When recommending a beach, tag it with '(Beach)' so the frontend displays the correct icon."
            . "\n- When recommending a restaurant, tag it with '(Restaurant)'."
            . "\n- When recommending a tourist spot or park, tag it with '(Tourist Spot)'."
            . "\n\nTRIP PARAMETERS:"
            . "\n- {$hotelDetails}"
            . "\n- Trip Duration: {$data['days']} day(s)"
            . "\n- Remaining daily allowance after hotel: roughly PHP " . number_format($dailyAllowance) . "/day (target ~PHP " . number_format($foodBudgetPerDay) . " for food, ~PHP " . number_format($activityBudgetPerDay) . " for activities and local transport)"
            . "\n- Transport note: Local Dagupan tricycle base fare is ~PHP 15–20 for short downtown hops, and ~PHP 40–60 to Bonuan/coastal areas. Factor transport into the daily activity cost."
            . "\n- Current Conditions: " . ($weatherDesc ?: 'not reported — plan for typical warm Dagupan weather; adjust for rain if mentioned')
            . "\n- Available nearby places: {$poiList}"
            . "\n- Rest schedule: {$restInstruction}"
            . "\n\nFORMATTING & STRUCTURE RULES (STRICT):"
            . "\n- Use '### Day X' as the heading for each regular day (or '### Full Rest Day' if the whole day is set for rest)."
            . "\n- Under each day, provide EXACTLY 3 to 4 chronological bullet points matching the day's flow:"
            . "\n    * Morning (approx 08:00–11:30): breakfast / morning activity"
            . "\n    * Afternoon (approx 13:00–17:00): afternoon spot or hotel downtime (respect rest window!)"
            . "\n    * Evening / Dinner (approx 18:00–21:00): dinner or light evening stop"
            . "\n    * Daily cost line: 'Estimated day cost: ~PHP X (Food ~PHP Y, Activities/Transpo ~PHP Z)'"
            . "\n- Respect the traveller's rest schedule: NEVER schedule an outside activity during their chosen rest window. If a rest window falls in that block, explicitly write '- [Time]: Rest at {$data['hotel']}'."
            . "\n- Keep each bullet concise (1–2 sentences) with an approximate time and approximate PHP cost."
            . "\n- No long prose paragraphs, no intro filler, no closing sign-offs."
            . "\n- End with a single short '### Trip Summary' section with 3 lines:"
            . "\n    * Total Estimated Cost: PHP X (Hotel PHP Y + Allowance PHP Z)"
            . "\n    * Remaining Buffer: PHP X"
            . "\n    * Practical Dagupan Tip: one concrete, actionable local tip (e.g. Bangus pasalubong at CSI Market Square, cash-only tricycles, sunset at Tondaligan).";
    }

    private function capNearbyPlaces(?string $raw, int $limit): string
    {
        if ($raw === null || trim($raw) === '') {
            return 'No nearby places found via online maps. Focus purely on hotel amenities.';
        }
        $items = array_filter(array_map('trim', explode('|', $raw)));
        $items = array_slice($items, 0, $limit);
        return implode(', ', $items);
    }

    private function resolveWholeRestDay(array $restList, int $days): ?int
    {
        if ($days < 2) {
            return null;
        }

        foreach (self::normalizeRestSchedule($restList) as $window) {
            if ($window['label'] === 'Whole Day') {
                return (int) ceil($days / 2);
            }
        }

        return null;
    }

    private function buildRestInstruction(array $restList, ?int $wholeRestDayIndex, int $days): string
    {
        $parts = [];

        foreach (self::normalizeRestSchedule($restList) as $window) {
            // Ang 'Whole Day' ay hiwalay na isinasaad sa ibaba.
            if ($window['label'] === 'Whole Day') {
                continue;
            }

            $hours = $window['minutes'] / 60;
            $duration = $hours >= 1 ? round($hours, 1) . ' hour(s)' : $window['minutes'] . ' minute(s)';

            $parts[] = $window['overnight']
                ? "rests overnight from {$window['start']} to {$window['end']} ({$duration}) — schedule no activities inside that window"
                : "rests daily from {$window['start']} to {$window['end']} ({$duration}) — schedule no activities inside that window";
        }

        if ($wholeRestDayIndex !== null) {
            $parts[] = "designate Day {$wholeRestDayIndex} as a full rest day with no scheduled activities";
        }

        if (empty($parts)) {
            return 'No specific rest preference — plan a full, active day each day.';
        }

        return 'The traveller ' . implode('; ', $parts) . '.';
    }

    /**
     * 'HH:MM' → minuto mula hatinggabi (sorting at overlap checks).
     */
    private static function minutesOf(string $time): int
    {
        return ((int) substr($time, 0, 2)) * 60 + ((int) substr($time, 3, 2));
    }

    /**
     * '14:00' → '2:00 PM' (kaparehong estilo ng dating hardcoded na oras).
     */
    private static function clockLabel(string $time): string
    {
        $hours = (int) substr($time, 0, 2);
        $minutes = substr($time, 3, 2);
        $display = $hours % 12 === 0 ? 12 : $hours % 12;

        return $display . ':' . $minutes . ' ' . ($hours >= 12 ? 'PM' : 'AM');
    }

    /**
     * Nasa loob ba ng alinman sa mga napiling rest window ang oras na ito?
     *
     * @param  array<int, array{start: string, end: string, overnight: bool}>  $windows
     */
    private static function isRestingAt(array $windows, string $time): bool
    {
        $at = self::minutesOf($time);

        foreach ($windows as $window) {
            $start = self::minutesOf($window['start']);
            $end = self::minutesOf($window['end']);

            $inside = $window['overnight']
                ? ($at >= $start || $at < $end)
                : ($at >= $start && $at < $end);

            if ($inside) {
                return true;
            }
        }

        return false;
    }

    private function generateLocalFallbackItinerary(array $data, array $restList, ?int $wholeRestDayIndex, array $budget, ?string $poiString = ''): string
    {
        $hotel = $data['hotel'];
        $days = $data['days'];
        $totalBudget = $data['budget'];

        // The POI list is passed in explicitly. It used to be read from
        // $data['nearby_places'], which is only present when the browser sends
        // it, so a server-side regeneration silently had no places to use.
        $pois = ($poiString === null || trim($poiString) === '')
            ? []
            : array_values(array_filter(array_map('trim', explode('|', $poiString))));
        $poiCount = count($pois);

        // Ang traveller mismo ang pumipili ng oras ng rest, kaya ang window (at
        // hindi isang label) ang sinusunod dito: lahat ng activity ay nailalagay
        // sa labas ng napiling oras.
        $timedRest = array_values(array_filter(
            self::normalizeRestSchedule($restList),
            fn ($window) => $window['label'] !== 'Whole Day'
        ));

        $markdown = "### Trip Overview\n";
        $markdown .= "- **{$hotel}**, PHP " . number_format($budget['nightlyRate']) . "/night for {$days} day(s) — total budget PHP " . number_format($totalBudget) . ".\n";
        $markdown .= "- Daily allowance after hotel: ~PHP " . number_format($budget['foodBudgetPerDay'] + $budget['activityBudgetPerDay']) . " (food + activities).\n";

        if (!empty($timedRest)) {
            $markdown .= "\n### Rest Schedule (daily)\n";
            foreach ($timedRest as $window) {
                $markdown .= "- {$window['start']}–{$window['end']}"
                    . ($window['overnight'] ? ' (overnight)' : '')
                    . " — no activities inside this window.\n";
            }
        }

        $markdown .= "\n";

        $poiIndex = 0;

        for ($i = 1; $i <= $days; $i++) {
            if ($wholeRestDayIndex !== null && $i === $wholeRestDayIndex) {
                $markdown .= "### Full Rest Day (Day {$i})\n";
                $markdown .= "- No activities scheduled — relax at the hotel, spa, or pool.\n";
                $markdown .= "- Optional: light walk around the neighborhood in the evening.\n\n";
                continue;
            }

            $markdown .= "### Day {$i}\n";

            $events = [];

            // Ang napiling rest window ang laging nakareserba bago pa man ang
            // anumang activity.
            foreach ($timedRest as $window) {
                $events[] = [
                    'at' => self::minutesOf($window['start']),
                    'text' => "- **" . self::clockLabel($window['start']) . ' – ' . self::clockLabel($window['end'])
                        . ":** Rest at {$hotel} — your chosen downtime.",
                ];
            }

            $candidates = [
                [
                    'at' => '09:00',
                    'usesPoi' => false,
                    'text' => "- **" . self::clockLabel('09:00') . ":** Breakfast near {$hotel}, then head out (~PHP " . number_format($budget['foodBudgetPerDay'] * 0.3) . ").",
                ],
                [
                    'at' => '15:30',
                    'usesPoi' => $poiCount > 0,
                    'text' => $poiCount > 0
                        ? "- **" . self::clockLabel('15:30') . ":** Visit **{$pois[$poiIndex % $poiCount]}** (~PHP " . number_format($budget['activityBudgetPerDay'] * 0.6) . " entrance/transport)."
                        : "- **" . self::clockLabel('15:30') . ":** Enjoy the amenities at {$hotel} since there are no nearby spots in our database.",
                ],
                [
                    'at' => '19:00',
                    'usesPoi' => false,
                    'text' => "- **" . self::clockLabel('19:00') . ":** Dinner near the hotel (~PHP " . number_format($budget['foodBudgetPerDay'] * 0.7) . ").",
                ],
            ];

            foreach ($candidates as $slot) {
                if (self::isRestingAt($timedRest, $slot['at'])) {
                    continue;
                }

                if ($slot['usesPoi']) {
                    $poiIndex++;
                }

                $events[] = ['at' => self::minutesOf($slot['at']), 'text' => $slot['text']];
            }

            usort($events, fn ($a, $b) => $a['at'] <=> $b['at']);

            if (empty($events)) {
                $markdown .= "- No activities scheduled — the whole day falls inside your rest schedule.\n";
            } else {
                foreach ($events as $event) {
                    $markdown .= $event['text'] . "\n";
                }
            }

            $markdown .= "\n";
        }

        $markdown .= "### Trip Summary\n";
        $markdown .= "- Estimated total spend stays within PHP " . number_format($totalBudget) . ".\n";
        $markdown .= "- Tip: confirm entrance fees and transport fares on-site, as prices can shift seasonally.\n";

        return $markdown;
    }
}