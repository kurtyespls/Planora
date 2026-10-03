<?php

namespace App\Services;

use App\Models\Hotel;
use App\Models\Location;
use App\Models\Plan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

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
     * Smallest daily allowance that can produce a real itinerary: roughly one
     * meal plus tricycle fares.
     *
     * The lodging guard alone is not enough. It only proves the budget covers
     * the hotel, never that anything is left over — and for a 1-day trip the
     * lodging cost is PHP 0, so *every* budget passed, down to the PHP 1 that
     * 'numeric|min:1' allows. The traveller got a full day-by-day itinerary
     * priced at one peso.
     *
     * Why PHP 200 rather than zero: the catalogue genuinely contains free
     * attractions (the Cathedral, the bangus landing centre, the city plaza),
     * so a zero floor would not be honest either — those trips are walkable but
     * not plannable. The cheapest restaurant in the catalogue is PHP 200 and a
     * tricycle is PHP 15-20, so PHP 200 is the point where "food and transport"
     * still works. Below the floor buildBudgetWarning() takes over as a softer
     * band (PHP 200-500 reads as "tight", PHP 500+ as comfortable).
     *
     * Public so the browser mirrors the rule instead of re-deriving it, the
     * same reason NIGHTS_PER_DAY_OFFSET is public (see planora.blade.php).
     */
    public const MIN_DAILY_ALLOWANCE = 200.0;

    /**
     * Largest total trip budget the planner will accept, in PHP.
     *
     * There used to be no ceiling at all — only `min:1` — so a stray keystroke
     * produced a PHP 123,000,000 daily allowance. Beyond this number the trip
     * stops being a budget and the picker becomes meaningless: every one of the
     * 47 catalogue places qualifies, so "swak sa budget mo" says nothing. It also
     * lets the picker cache mint a fresh key per peso typed.
     *
     * Comfortably above any real Dagupan trip while still catching typos.
     */
    public const MAX_TRIP_BUDGET = 1000000.0;

    /**
     * Format ng custom na rest window na ipinapadala ng browser.
     */
    private const REST_WINDOW_PATTERN = '/^([01]\d|2[0-3]):([0-5]\d)-([01]\d|2[0-3]):([0-5]\d)$/';

    /**
     * Category vocabulary shared by the place picker, the map legend, the AI
     * prompt tags and the saved-plan labels. These keys are the Location
     * constants on purpose so a category can never be spelled one way in the
     * seeders and another way here.
     */
    public const PLACE_CATEGORY_LABELS = [
        Location::CATEGORY_RESTAURANT => 'Restaurant',
        Location::CATEGORY_MALL       => 'Mall',
        Location::CATEGORY_BEACH      => 'Beach',
        Location::CATEGORY_TOURIST    => 'Tourist Spot',
    ];

    /**
     * How many places a traveller may pin before the AI is sent off to build an
     * itinerary. Past this the prompt stops being a schedule and starts being a
     * grocery list, which is worse for the traveller than letting the AI choose.
     *
     * Mirrored as MAX_SELECTED_PLACES in planora.blade.php so the picker disables
     * its last checkbox instead of letting the server reject a submission the
     * traveller believed was valid.
     */
    public const MAX_SELECTED_PLACES = 12;

    /**
     * Per-category ceiling on those picks. Without it a traveller who ticks six
     * restaurants crowds the other three categories out of the list entirely.
     */
    public const MAX_SELECTED_PLACES_PER_CATEGORY = 4;

    /**
     * How many Overpass places may be offered to the model on top of the
     * traveller's own picks. Kept separate from the picks because the two are
     * not interchangeable: a pick is a promise, an Overpass result is a
     * suggestion, and only the suggestions are allowed to be trimmed.
     */
    public const NEARBY_PLACE_PROMPT_LIMIT = 10;

    /**
     * Shortest a pick may be before it is allowed to fuzzy-match a catalogue
     * row. Only guards against one- or two-character prefixes; the real
     * discrimination is done by MIN_FUZZY_PICK_RATIO.
     */
    private const MIN_FUZZY_PICK_LENGTH = 4;

    /**
     * How much of the longer key the shorter one must account for before a
     * fuzzy match is trusted.
     *
     * 0.7 keeps typo-tolerance working ("Tondaligan Blue Beac" still finds
     * "Tondaligan Blue Beach") while rejecting the case that actually matters:
     * a bare category word. "beach" is 5 of the 21 characters in
     * "Tondaligan Blue Beach" — 24% — so it no longer silently resolves to
     * whichever coastal place happens to be first in the catalogue.
     */
    private const MIN_FUZZY_PICK_RATIO = 0.7;

    /**
     * Granularity of the place-catalogue cache key. See
     * CacheService::getBudgetFriendlyPlaces().
     */
    private const ALLOWANCE_BUCKET_SIZE = 50;

    /** Radius in metres used when no origin is known — Dagupan's city proper. */
    private const EARTH_RADIUS_M = 6371000;

    /**
     * Ang AI ay madaling mag-24-hour kahit 12-hour ang hinihingi, kaya ang
     * natitirang 'HH:MM' na HINDI sinusundan ng AM/PM ang kinokonberte (tingnan
     * ang toTwelveHourClock()). Ang negative lookahead ang nagpoprotekta sa mga
     * 12-hour na oras na galing na sa AI o sa local generator ('3:30 PM').
     */
    private const CLOCK_24H_PATTERN = '/\b([01]?\d|2[0-3]):([0-5]\d)\b(?!\s*[AaPp]\.?[Mm]\.?)/';

    /**
     * Isinisingit sa prompt para 12-hour ang isulat ng AI.
     */
    public const AI_TIME_FORMAT_RULE = 'Write EVERY time in 12-hour clock with AM/PM (e.g. 8:00 AM, 2:30 PM, 6:15 PM). NEVER use 24-hour times like 14:00 or 18:30.';

    public static function nightsFor(int $days): int
    {
        return max(0, $days - self::NIGHTS_PER_DAY_OFFSET);
    }

    /**
     * Money left per day once lodging is paid for.
     *
     * This is the one number three surfaces agree on: the server-side budget
     * guard in buildItinerary(), the browser's #budget-hint estimate, and the
     * place picker's eligibility filter. Deriving it in one place is what stops
     * the picker from offering a place the guard would then reject.
     *
     * @param float $nightlyRate  Hotel rate per night.
     * @param float $budget       Total trip budget in PHP.
     */
    public static function dailyAllowanceFor(float $nightlyRate, float $budget, int $days): float
    {
        if ($days <= 0) {
            return 0.0;
        }

        $lodging = $nightlyRate * self::nightsFor($days);

        return max(0.0, ($budget - $lodging) / $days);
    }

    /**
     * Normalised comparison key for a place name: lowercased, de-accented and
     * stripped of everything that is not a letter or digit.
     *
     * The browser sends back whatever string was in the dataset, so "SM Center
     * Dagupan" and "sm center dagupan" have to resolve to the same catalogue row.
     */
    public static function placeKey(string $value): string
    {
        return (string) preg_replace('/[^a-z0-9]+/', '', Str::ascii(Str::lower(trim($value))));
    }

    /**
     * Great-circle distance in kilometres, or null when either end is unknown.
     * Distances are only ever shown as a "how far am I walking" hint, so null is
     * an honest answer for a POI or a hotel that was never geocoded.
     */
    public static function distanceKm(?float $lat1, ?float $lon1, ?float $lat2, ?float $lon2): ?float
    {
        if ($lat1 === null || $lon1 === null || $lat2 === null || $lon2 === null) {
            return null;
        }

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;

        return round(self::EARTH_RADIUS_M * 2 * atan2(sqrt($a), sqrt(1 - $a)) / 1000, 2);
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
     * kung label, o "2:00 PM–4:00 PM (2h)" kung window.
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

        // Ang buong araw (00:00–23:59) ay 'Whole Day' pa rin ang label —
        // kaparehong rule sa normalizeRestSchedule().
        if ($window['start'] === '00:00' && $window['end'] === '23:59') {
            return 'Whole Day';
        }

        $hours = $window['minutes'] / 60;
        $duration = $hours >= 1 ? round($hours, 1) . 'h' : $window['minutes'] . 'm';

        // 12-hour ang display kahit 24-hour ang naka-save ('14:00-16:00').
        return self::clockLabel($window['start']) . '–' . self::clockLabel($window['end']) . ' (' . $duration . ')';
    }

    /**
     * Ang presets ay naka-store bilang 'HH:MM' (para sa native time inputs at sa
     * validation), kaya hiwalay na display copy ang ibinibigay sa mga UI chip.
     *
     * @return array<string, array{0: string, 1: string}>
     */
    public static function restWindowDisplay(): array
    {
        $display = [];

        foreach (self::REST_WINDOWS as $label => $window) {
            $display[$label] = [self::clockLabel($window[0]), self::clockLabel($window[1])];
        }

        return $display;
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
     * Curated Dagupan points of interest that fit what the traveller can spend
     * per day, ordered by how close they are to the stay they picked. This backs
     * the Step 03 place picker.
     *
     * The seeded `locations` catalogue is the only source that carries a real
     * price — the Overpass results behind the map pins have no cost field at all
     * — so it is the only thing that can honestly answer "does this fit my
     * budget". Places that do *not* fit are still returned, flagged, because
     * hiding them makes a thin budget look like a thin city.
     *
     * @param  float|null  $lat  Origin latitude (the chosen hotel) for distance hints.
     * @param  float|null  $lon  Origin longitude.
     * @return array{daily_allowance: float, origin_known: bool, within_budget: array<string, array<int, array<string, mixed>>>, over_budget: array<string, array<int, array<string, mixed>>>}
     */
    public function getBudgetFriendlyPlaces(float $dailyAllowance, ?float $lat = null, ?float $lon = null): array
    {
        $dailyAllowance = max(0.0, $dailyAllowance);
        $bucket = (int) (round($dailyAllowance / self::ALLOWANCE_BUCKET_SIZE) * self::ALLOWANCE_BUCKET_SIZE);

        $rows = CacheService::getBudgetFriendlyPlaces($bucket, fn () => Location::query()
            ->whereIn('category', array_keys(self::PLACE_CATEGORY_LABELS))
            ->get(['name', 'latitude', 'longitude', 'rating', 'category', 'icon', 'budgetPerDay', 'description'])
            ->map(fn (Location $place) => [
                'name' => $place->name,
                'category' => $place->category,
                'icon' => $place->icon ?: '📍',
                'rating' => (float) $place->rating,
                'budget_per_day' => (float) $place->budgetPerDay,
                'description' => (string) $place->description,
                'lat' => (float) $place->latitude,
                'lon' => (float) $place->longitude,
            ])
            ->all());

        $within = array_fill_keys(array_keys(self::PLACE_CATEGORY_LABELS), []);
        $over = $within;

        foreach ($rows as $row) {
            $category = $row['category'];

            if (!isset($within[$category])) {
                continue;
            }

            $row['distance_km'] = self::distanceKm($lat, $lon, $row['lat'], $row['lon']);
            $row['fits_budget'] = $row['budget_per_day'] <= $dailyAllowance;

            if ($row['fits_budget']) {
                $within[$category][] = $row;
            } else {
                $over[$category][] = $row;
            }
        }

        // Nearest to the hotel first. Ang layunin ng picker ay matabilo ang
        // paglalakbay, kaya ang distansya ang pangunahing pagkakasunod-sunod — ang
        // rating ay nasa pagkatapos bilang tie-breaker at pampababa ng pagkakaiba.
        //
        // Kapag walang alam na hotel coordinates (hal. hindi pa na-geocode ang
        // stay), walang maipapakitang distansya — sa kasalungat ay rating na
        // ang batayan para hindi na kasing-pareho ang lahat ng pagkakasunod-sunod.
        $originKnown = $lat !== null && $lon !== null;

        $byDistance = function (array $a, array $b): int {
            if ($a['distance_km'] === null && $b['distance_km'] === null) {
                return $b['rating'] <=> $a['rating'];
            }

            // Ang hindi pa naka-geocode ay pinatagong dulo, hindi nangunguna.
            if ($a['distance_km'] === null) {
                return 1;
            }

            if ($b['distance_km'] === null) {
                return -1;
            }

            return $a['distance_km'] <=> $b['distance_km']
                ?: $b['rating'] <=> $a['rating']
                ?: strcmp($a['name'], $b['name']);
        };

        $byRating = fn (array $a, array $b): int => $b['rating'] <=> $a['rating']
            ?: strcmp($a['name'], $b['name']);

        foreach ([&$within, &$over] as &$group) {
            foreach ($group as &$places) {
                usort($places, $originKnown ? $byDistance : $byRating);
            }
        }
        unset($group, $places);

        return [
            'daily_allowance' => round($dailyAllowance, 2),
            // Ang browser ay nagpapakita ng "nearest first" na label lamang
            // kapag totoo ito — kung hindi, ipinapakita nito ang rating-based
            // na label upang hindi maging maling pahayag.
            'origin_known' => $originKnown,
            'within_budget' => $within,
            'over_budget' => $over,
        ];
    }

    /**
     * Resolve the traveller's free-form picks into canonical "Name (Category)"
     * labels that both the AI prompt and the map pins already understand.
     *
     * Anything that matches no catalogue row is dropped rather than passed
     * through. The prompt's first rule forbids inventing places, so forwarding an
     * unrecognised string would be feeding the model exactly the kind of
     * hallucination seed that rule exists to stop.
     *
     * @param  array<int, mixed>  $selectedPlaces
     * @return array<int, string>
     */
    public function selectedPlaceLabels(array $selectedPlaces): array
    {
        $wanted = [];

        foreach ($selectedPlaces as $name) {
            if (!is_string($name)) {
                continue;
            }

            $name = trim(strip_tags($name));

            if ($name !== '') {
                $wanted[] = $name;
            }
        }

        $wanted = array_slice(array_values(array_unique($wanted)), 0, self::MAX_SELECTED_PLACES);

        if ($wanted === []) {
            return [];
        }

        $catalogue = [];

        foreach (Location::query()
            ->whereIn('category', array_keys(self::PLACE_CATEGORY_LABELS))
            ->get(['name', 'category']) as $place) {
            $catalogue[self::placeKey($place->name)] = $place->name
                . ' (' . self::PLACE_CATEGORY_LABELS[$place->category] . ')';
        }

        $labels = [];

        foreach ($wanted as $name) {
            $key = self::placeKey($name);

            if ($key === '') {
                continue;
            }

            if (isset($catalogue[$key])) {
                $labels[] = $catalogue[$key];
                continue;
            }

            // Fall back to a fuzzy match so a traveller who edits a name, or a
            // browser that trimmed punctuation, still lands on the right row.
            //
            // Dati ito ay plain substring matching, na malaking problema: ang
            // "beach" ay substring ng halos lahat ng coastal catalogue, kaya
            // naaabot nito ang unang row na masyado. Ngayon, tatlong dapat
            // sabay: sapat ang haba, dapat isa sa dalawa ay panimula ng isa,
            // at dapat saklawin ng maikli ang malaking bahagi nito. Ang huling
            // isa ang tumatigil sa "beach" na pagkakamali — at ang paghula nang
            // mali ay mas masama kaysa pagbitawan, dahil ang na-bitawan ay
            // makikita (wala ito sa itinerary).
            if (mb_strlen($key) < self::MIN_FUZZY_PICK_LENGTH) {
                continue;
            }

            foreach ($catalogue as $candidateKey => $label) {
                if ($candidateKey === '') {
                    continue;
                }

                if (!str_starts_with($candidateKey, $key) && !str_starts_with($key, $candidateKey)) {
                    continue;
                }

                $shorter = min(mb_strlen($key), mb_strlen($candidateKey));
                $longer = max(mb_strlen($key), mb_strlen($candidateKey));

                if ($shorter / $longer < self::MIN_FUZZY_PICK_RATIO) {
                    continue;
                }

                $labels[] = $label;
                continue 2;
            }
        }

        return array_slice(array_values(array_unique($labels)), 0, self::MAX_SELECTED_PLACES);
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
                // Ang naresolve na canonical labels, hindi ang raw na string ng
                // browser, para pareho ang babasahin sa profile at sa plan page.
                'selected_places' => $result['selected_places'],
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
            // Ang dating picks ang batayan ng muling pagkakaayos: ang "Regenerate"
            // ay dapat makapagbigay ng ibang bersyon ng parehong plano, hindi ng
            // ibang plano.
            'selected_places' => $plan->selected_places ?? [],
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

        $dailyAllowance = self::dailyAllowanceFor($nightlyRate, (float) $validated['budget'], $days);

        // REGRESSION FIX: ang tanging guard ay naglalabas lang kung bayad na ang
        // budget sa hotel — hindi kailanman kung may natitira para mag expenses.
        // Sa isang araw, nights = 0 kaya PHP 0 ang lodging, kaya '1 < 0' ay
        // mali at dumaan pa rin ang PHP 1. Kailangan ng hiwalay na floor.
        if ($dailyAllowance < self::MIN_DAILY_ALLOWANCE) {
            $requiredTotal = $totalHotelCost + (self::MIN_DAILY_ALLOWANCE * $days);
            $shortfall = $requiredTotal - (float) $validated['budget'];

            return [
                'error' => 'Budget too low for a real itinerary. After accommodation you have about PHP '
                    . number_format($dailyAllowance) . ' per day left, but Planora needs at least PHP '
                    . number_format(self::MIN_DAILY_ALLOWANCE)
                    . '/day — one meal plus tricycle fares. For ' . $days . ' day(s) that means PHP '
                    . number_format($requiredTotal) . ' in total. Please add PHP '
                    . number_format($shortfall) . ' to your budget, or pick a cheaper stay.',
            ];
        }

        $foodBudgetPerDay = round($dailyAllowance * 0.6);
        $activityBudgetPerDay = round($dailyAllowance * 0.4);

        // Conditions are resolved here rather than trusted from the client, and
        // they also drive the practical advice attached to the budget warning.
        $weatherDesc = $weatherDesc ?: $this->resolveWeatherSummary($hotel);
        $budgetWarning = $this->buildBudgetWarning($hotel, $dailyAllowance, $days, $weatherDesc);

        $restList = $validated['rest_days'] ?? [];
        $wholeRestDayIndex = $this->resolveWholeRestDay($restList, $days);
        $restInstruction = self::buildRestInstruction($restList, $wholeRestDayIndex, $days);

        // A regeneration arrives without a browser-supplied POI list, so the
        // places are looked up from the hotel coordinates instead.
        if ($resolvePlacesWhenMissing && trim((string) $nearbyPlaces) === '') {
            $nearbyPlaces = $this->describeNearbyPlaces($hotel);
        }

        // Ang mga pinili ng traveller sa Step 03. Nililista muna sila bago ang
        // Overpass listahan kasi doon pinuputol mula sa dulo.
        $selectedPlaceLabels = $this->selectedPlaceLabels($validated['selected_places'] ?? []);
        $combinedPlaces = $this->prependPlaces($nearbyPlaces, $selectedPlaceLabels);
        $poiList = $this->allowedPlacesForPrompt($combinedPlaces, $selectedPlaceLabels);

        $prompt = $this->buildAiPrompt(
            $validated,
            $hotel,
            $nightlyRate,
            $dailyAllowance,
            $foodBudgetPerDay,
            $activityBudgetPerDay,
            $weatherDesc,
            $poiList,
            $restInstruction,
            $selectedPlaceLabels
        );

        $apiKey = config('services.groq.key');
        $recommendation = $apiKey ? $this->callGroqApi($prompt, $apiKey) : null;
        $aiProvider = empty($recommendation) ? 'local' : 'groq';

        if (empty($recommendation)) {
            // Ang parehong listahan ang ipinapasa sa local generator, kaya hindi
            // nagiging "offline mode" na tulad ng AI ang dating picks ng traveller.
            $recommendation = $this->generateLocalFallbackItinerary(
                $validated, $restList, $wholeRestDayIndex,
                [
                    'nightlyRate' => $nightlyRate,
                    'foodBudgetPerDay' => $foodBudgetPerDay,
                    'activityBudgetPerDay' => $activityBudgetPerDay,
                ],
                $combinedPlaces,
                $selectedPlaceLabels
            );
        }

        // Lahat ng oras na makikita ng traveller ay 12-hour, kahit 24-hour ang
        // isinulat ng AI. Idempotent ito para sa output ng local generator.
        $recommendation = self::toTwelveHourClock($recommendation);

        return [
            'recommendation' => $recommendation,
            'budget_warning' => $budgetWarning,
            'daily_allowance' => round($dailyAllowance),
            'nights' => $nights,
            'ai_provider' => $aiProvider,
            // Ibinabalik para maisama sa Plan::create() at para magamit ng
            // browser ang parehong listahan sa map pins.
            'selected_places' => $selectedPlaceLabels,
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
        $names = [];

        foreach (self::PLACE_CATEGORY_LABELS as $type => $label) {
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
        string $restInstruction,
        array $selectedPlaceLabels = []
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

        // Pinangungahan nito ang "don't repeat stops" rule: kahit paulitin ng
        // AI ang isang lugar dahil kakaunti ang araw, mas importante ang pagsunod
        // sa pinili ng traveller.
        $pickInstruction = $selectedPlaceLabels === []
            ? 'The traveller did not pick any specific places — choose the best matches for them from the list below.'
            : 'THE TRAVELLER EXPLICITLY CHOSE THESE PLACES AND YOU MUST SCHEDULE EVERY ONE OF THEM AT LEAST ONCE: '
                . implode(', ', $selectedPlaceLabels)
                . "\n- This OVERRIDES your own judgement about pacing, variety and how many stops fit in a day."
                . "\n- Spread them across DIFFERENT days. Never put two of them on the same day unless the trip is only 1-2 days long."
                . "\n- Their real prices were already filtered against this traveller's budget, so schedule them at their true cost, not at a cheaper invented one."
                . "\n- Only add further places beyond these if there is room left in the day.";

        return "You are PLANORA, an expert local travel planner for Dagupan City, Pangasinan, Philippines."
            . "\n\nCRITICAL RULE #0 — THE TRAVELLER'S PICKS: " . $pickInstruction
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
            . "\n- TIME FORMAT: " . self::AI_TIME_FORMAT_RULE
            . "\n- Use '### Day X' as the heading for each regular day (or '### Full Rest Day' if the whole day is set for rest)."
            . "\n- Under each day, provide EXACTLY 3 to 4 chronological bullet points matching the day's flow:"
            . "\n    * Morning (approx 8:00 AM–11:30 AM): breakfast / morning activity"
            . "\n    * Afternoon (approx 1:00 PM–5:00 PM): afternoon spot or hotel downtime (respect rest window!)"
            . "\n    * Evening / Dinner (approx 6:00 PM–9:00 PM): dinner or light evening stop"
            . "\n    * Daily cost line: 'Estimated day cost: ~PHP X (Food ~PHP Y, Activities/Transpo ~PHP Z)'"
            . "\n- Respect the traveller's rest schedule: NEVER schedule an outside activity during their chosen rest window. If a rest window falls in that block, explicitly write '- [Time]: Rest at {$data['hotel']}'."
            . "\n- Keep each bullet concise (1–2 sentences) with an approximate time and approximate PHP cost."
            . "\n- No long prose paragraphs, no intro filler, no closing sign-offs."
            . "\n- End with a single short '### Trip Summary' section with 3 lines:"
            . "\n    * Total Estimated Cost: PHP X (Hotel PHP Y + Allowance PHP Z)"
            . "\n    * Remaining Buffer: PHP X"
            . "\n    * Practical Dagupan Tip: one concrete, actionable local tip (e.g. Bangus pasalubong at CSI Market Square, cash-only tricycles, sunset at Tondaligan).";
    }

    /**
     * The comma-separated "you may only use these" list for CRITICAL RULE #1.
     *
     * The cap has to leave room for every pick, not just ten entries in total.
     * Because prependPlaces() puts the picks first, a flat limit silently
     * deleted the last picks — so RULE #0 ("schedule every one of these") and
     * RULE #1 ("only use this list") contradicted each other, and the AI was
     * being pushed toward inventing a stand-in for the places that vanished.
     *
     * Public so the behaviour can be asserted directly instead of inferred from
     * generated prose.
     *
     * @param  array<int, string>  $selectedPlaceLabels
     */
    public function allowedPlacesForPrompt(string $combinedPlaces, array $selectedPlaceLabels): string
    {
        return $this->capNearbyPlaces($combinedPlaces, count($selectedPlaceLabels) + self::NEARBY_PLACE_PROMPT_LIMIT);
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

    /**
     * Put the traveller's picks at the head of the pipe-delimited POI list.
     *
     * capNearbyPlaces() truncates from the tail, so without this a full Overpass
     * list would quietly push out exactly the places the traveller deliberately
     * ticked. The pipe is stripped from each label because it is the delimiter —
     * a place name containing one would otherwise split into two fake entries.
     */
    private function prependPlaces(?string $nearbyPlaces, array $selectedPlaceLabels): string
    {
        if ($selectedPlaceLabels === []) {
            return (string) $nearbyPlaces;
        }

        $picks = implode('|', array_map(
            fn (string $label) => str_replace('|', ' ', $label),
            $selectedPlaceLabels
        ));

        return trim((string) $nearbyPlaces) === '' ? $picks : $picks . '|' . $nearbyPlaces;
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

    /**
     * Ang rest window ay isinasalin sa 12-hour na oras para sa prompt (para
     * 12-hour din ang isulat ng AI). Public para direktang matest.
     */
    public static function buildRestInstruction(array $restList, ?int $wholeRestDayIndex, int $days): string
    {
        $parts = [];

        foreach (self::normalizeRestSchedule($restList) as $window) {
            // Ang 'Whole Day' ay hiwalay na isinasaad sa ibaba.
            if ($window['label'] === 'Whole Day') {
                continue;
            }

            $hours = $window['minutes'] / 60;
            $duration = $hours >= 1 ? round($hours, 1) . ' hour(s)' : $window['minutes'] . ' minute(s)';

            // 12-hour na oras sa prompt (hal. 2:00 PM to 4:00 PM).
            $start = self::clockLabel($window['start']);
            $end = self::clockLabel($window['end']);

            $parts[] = $window['overnight']
                ? "rests overnight from {$start} to {$end} ({$duration}) — schedule no activities inside that window"
                : "rests daily from {$start} to {$end} ({$duration}) — schedule no activities inside that window";
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
     * Public dahil ito rin ang ginagamit ng UI chips at ng rest labels.
     */
    public static function clockLabel(string $time): string
    {
        $hours = (int) substr($time, 0, 2);
        $minutes = substr($time, 3, 2);
        $display = $hours % 12 === 0 ? 12 : $hours % 12;

        return $display . ':' . $minutes . ' ' . ($hours >= 12 ? 'PM' : 'AM');
    }

    /**
     * Ang natitirang 24-hour na oras sa isang text ay ginagawang 12-hour. Ang
     * oras na may AM/PM ay hindi ginalaw, kaya safe itong patakbuhin kahit sa
     * output ng local generator (idempotent).
     */
    public static function toTwelveHourClock(?string $text): ?string
    {
        if ($text === null || $text === '') {
            return $text;
        }

        return (string) preg_replace_callback(
            self::CLOCK_24H_PATTERN,
            static fn (array $matches): string => self::clockLabel(
                str_pad($matches[1], 2, '0', STR_PAD_LEFT) . ':' . $matches[2]
            ),
            $text
        );
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

    private function generateLocalFallbackItinerary(array $data, array $restList, ?int $wholeRestDayIndex, array $budget, ?string $poiString = '', array $selectedPlaceLabels = []): string
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

        if ($selectedPlaceLabels !== []) {
            $markdown .= "- Your picks, locked in: " . implode(', ', $selectedPlaceLabels) . ".\n";
        }

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