<?php

namespace Tests\Feature;

use App\Models\Hotel;
use App\Models\Location;
use App\Models\Plan;
use App\Models\User;
use App\Services\PlanoraService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Step 03 ang place picker: ang mga mall/beach/restaurant/tourist spot na
 * sinasabi ng endpoint na "swak sa budget mo", at ang pagiging tunay na
 * napapansin ng itinerary generator ang mga pinili ng traveller.
 *
 * Deterministic: walang AI call kailangan. Walang GROQ_API_KEY sa phpunit.xml,
 * kaya ang local generator ang tumutugma at ang picks ay dapat makita roon.
 */
class PlacePickerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Ang regeneratePlan() ay tumatawag sa Overpass para sa mga lugar malapit
        // sa hotel. Walang network sa test suite — ang 30s timeout ng Overpass
        // ay magpapalamoy sa buong suite.
        Http::preventStrayRequests();
    }

    private function makeHotel(): Hotel
    {
        return Hotel::create([
            'name' => 'The Budget Inn',
            'address' => 'M.H. del Pilar Street, Dagupan City',
            'price' => 1000,
            'rating' => 8.0,
            'description' => 'A test hotel.',
            'amenities' => 'Pool, WiFi',
            'image_url' => '/images/test.jpg',
            'lat' => 16.0438,
            'lon' => 120.3331,
        ]);
    }

    private function makeLocation(string $name, string $category, float $cost): Location
    {
        return Location::create([
            'name' => $name,
            'location' => 'Dagupan City',
            'latitude' => 16.0450,
            'longitude' => 120.3400,
            'rating' => 8.5,
            'category' => $category,
            'icon' => '📍',
            'budgetPerDay' => $cost,
            'description' => 'A test place.',
        ]);
    }

    private function makeLocated(string $name, string $category, float $cost, float $rating, float $lat, float $lon): Location
    {
        $place = $this->makeLocation($name, $category, $cost);
        $place->update(['rating' => $rating, 'latitude' => $lat, 'longitude' => $lon]);

        return $place->fresh();
    }

    public function test_endpoint_only_offers_places_within_the_daily_allowance(): void
    {
        $this->makeLocation('Cheap Mall', Location::CATEGORY_MALL, 200);
        $this->makeLocation('Pricey Beach', Location::CATEGORY_BEACH, 900);

        $response = $this->getJson('/api/places?daily_allowance=500');

        $response->assertOk()->assertJsonPath('daily_allowance', 500);

        $this->assertSame(['Cheap Mall'], array_column($response->json('within_budget.mall'), 'name'));
        $this->assertSame(['Pricey Beach'], array_column($response->json('over_budget.beach'), 'name'));

        // Ang over-budget na beach ay wala sa listahan ng mapipipili.
        $allNames = [];
        foreach ($response->json('within_budget') as $list) {
            $allNames = array_merge($allNames, array_column($list, 'name'));
        }
        $this->assertNotContains('Pricey Beach', $allNames);
    }

    public function test_a_place_costing_exactly_the_allowance_still_counts_as_fitting(): void
    {
        $this->makeLocation('Exact Fit Restaurant', Location::CATEGORY_RESTAURANT, 500);

        $response = $this->getJson('/api/places?daily_allowance=500');

        $response->assertOk();
        $this->assertSame(
            ['Exact Fit Restaurant'],
            array_column($response->json('within_budget.restaurant'), 'name')
        );
        $this->assertSame([], $response->json('over_budget.restaurant'));
    }

    public function test_endpoint_never_returns_a_negative_allowance(): void
    {
        $this->makeLocation('Cheap Mall', Location::CATEGORY_MALL, 200);

        $this->getJson('/api/places?daily_allowance=-5000')
            ->assertOk()
            ->assertJsonPath('daily_allowance', 0);
    }

    public function test_places_are_ordered_nearest_to_the_hotel_first(): void
    {
        // Ang pinakabait rating ang pinakamalayo — dapat talo ng distansya.
        $this->makeLocated('Far But Great', Location::CATEGORY_MALL, 200, 9.9, 16.0800, 120.4000);
        $this->makeLocated('Near And Middling', Location::CATEGORY_MALL, 200, 7.0, 16.0450, 120.3400);
        $this->makeLocated('Middle And Good', Location::CATEGORY_MALL, 200, 8.0, 16.0600, 120.3600);

        $response = $this->getJson('/api/places?daily_allowance=1000&lat=16.0438&lon=120.3331');

        $response->assertOk()->assertJsonPath('origin_known', true);

        $this->assertSame(
            ['Near And Middling', 'Middle And Good', 'Far But Great'],
            array_column($response->json('within_budget.mall'), 'name')
        );
    }

    public function test_nearest_ordering_also_applies_across_categories(): void
    {
        $this->makeLocated('Far Restaurant', Location::CATEGORY_RESTAURANT, 200, 9.9, 16.0900, 120.4200);
        $this->makeLocated('Near Mall', Location::CATEGORY_MALL, 200, 7.0, 16.0440, 120.3340);

        $response = $this->getJson('/api/places?daily_allowance=1000&lat=16.0438&lon=120.3331');

        // Bawat kategorya ay may sariling ayos; ang browser ang nagsasama at
        // nag-aayos ulit ng "All". Ang endpoint ay totoo pa rin sa bawat isa.
        $this->assertSame(['Near Mall'], array_column($response->json('within_budget.mall'), 'name'));
        $this->assertSame(['Far Restaurant'], array_column($response->json('within_budget.restaurant'), 'name'));

        $nearMall = $response->json('within_budget.mall.0.distance_km');
        $farRestaurant = $response->json('within_budget.restaurant.0.distance_km');

        $this->assertLessThan($farRestaurant, $nearMall);
    }

    public function test_distance_is_null_and_rating_orders_the_list_without_a_hotel_location(): void
    {
        $this->makeLocated('Lower Rated', Location::CATEGORY_MALL, 200, 7.0, 16.0450, 120.3400);
        $this->makeLocated('Higher Rated', Location::CATEGORY_MALL, 200, 9.0, 16.0800, 120.4000);

        $response = $this->getJson('/api/places?daily_allowance=1000');

        $response->assertOk()->assertJsonPath('origin_known', false);

        // Walang maipapakitang distansya, kaya huwag magpakita ng "nearest
        // first" — rating na ang batayan.
        $this->assertNull($response->json('within_budget.mall.0.distance_km'));
        $this->assertSame(
            ['Higher Rated', 'Lower Rated'],
            array_column($response->json('within_budget.mall'), 'name')
        );
    }

    public function test_selected_places_are_saved_and_appear_in_the_itinerary(): void
    {
        $hotel = $this->makeHotel();
        $this->makeLocation('SM Center Dagupan', Location::CATEGORY_MALL, 300);
        $this->makeLocation('Tondaligan Blue Beach', Location::CATEGORY_BEACH, 100);
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/generate-plan', [
            'hotel' => $hotel->name,
            'budget' => 5000,
            'days' => 3,
            'rest_days' => [],
            'selected_places' => ['SM Center Dagupan', 'Tondaligan Blue Beach'],
        ]);

        $response->assertOk();

        // Ibinabalik ng server ang canonical na "Name (Category)" labels.
        $response->assertJsonPath('selected_places.0', 'SM Center Dagupan (Mall)');
        $response->assertJsonPath('selected_places.1', 'Tondaligan Blue Beach (Beach)');

        $plan = Plan::query()->latest('id')->first();
        $this->assertSame(
            ['SM Center Dagupan (Mall)', 'Tondaligan Blue Beach (Beach)'],
            $plan->selected_places
        );

        // Ang local generator ay gumagamit ng parehong listahan, kaya hindi
        // nagiging "walang epekto" ang picker kapag walang AI key na naka-configure.
        $this->assertStringContainsString('SM Center Dagupan (Mall)', $plan->ai_recommendation);
        $this->assertStringContainsString('Tondaligan Blue Beach (Beach)', $plan->ai_recommendation);
    }

    public function test_unknown_or_marked_up_picks_are_dropped_rather_than_forwarded_to_the_ai(): void
    {
        $hotel = $this->makeHotel();
        $this->makeLocation('SM Center Dagupan', Location::CATEGORY_MALL, 300);
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/generate-plan', [
            'hotel' => $hotel->name,
            'budget' => 5000,
            'days' => 2,
            'selected_places' => [
                'SM Center Dagupan',
                'Totally Made Up Bistro',
                '<script>alert(1)</script>SM Center Dagupan',
            ],
        ]);

        $response->assertOk();

        // Isang beses lang ang SM Center Dagupan: pareho ang naresolve at
        // tinatapon ang duplikato. Ang wala sa katalogo ay na-drop.
        $response->assertJsonPath('selected_places', ['SM Center Dagupan (Mall)']);
        $this->assertStringNotContainsString('Made Up Bistro', $response->json('recommendation'));
    }

    public function test_more_picks_than_the_cap_is_rejected(): void
    {
        $hotel = $this->makeHotel();
        $user = User::factory()->create();

        $tooMany = [];
        for ($i = 0; $i <= PlanoraService::MAX_SELECTED_PLACES; $i++) {
            $tooMany[] = "Place {$i}";
        }

        $this->actingAs($user)->postJson('/generate-plan', [
            'hotel' => $hotel->name,
            'budget' => 5000,
            'days' => 2,
            'selected_places' => $tooMany,
        ])->assertStatus(422)->assertJsonValidationErrors('selected_places');
    }

    public function test_every_pick_survives_the_prompt_poi_cap(): void
    {
        // REGRESSION: capNearbyPlaces() pinuputol mula sa dulo. Noon, ang mga
        // pinili ang unang nakalagay kaya kapag sobra sa 10 ang pinili, ang
        // mga huli ay nawawala sa Rule #1 listahan — habang ang Rule #0 ay
        // nagsasabing "i-schedule lahat". Nagkakaroon ng direktang kasalungat sa
        // prompt, at ang AI ay maaaring mag-hallucinate para sa nawala.
        $hotel = $this->makeHotel();
        $user = User::factory()->create();

        $names = [];
        for ($i = 1; $i <= PlanoraService::MAX_SELECTED_PLACES; $i++) {
            $name = "Pick {$i}";
            $this->makeLocation($name, Location::CATEGORY_MALL, 100);
            $names[] = $name;
        }

        $this->assertCount(PlanoraService::MAX_SELECTED_PLACES, $names);

        $response = $this->actingAs($user)->postJson('/generate-plan', [
            'hotel' => $hotel->name,
            'budget' => 20000,
            'days' => 5,
            // Isang sapat na haba ng Overpass listahan para masubukan ang cap.
            'nearby_places' => implode('|', array_map(
                fn ($i) => "Overpass Spot {$i} (Mall)",
                range(1, 10)
            )),
            'selected_places' => $names,
        ]);

        $response->assertOk();
        $this->assertCount(PlanoraService::MAX_SELECTED_PLACES, $response->json('selected_places'));

        // Bawat pinili ay dapat makarating sa mismong "allowed places" listahan ng
        // prompt, kahit 12 na ang pinili at 10 pa rin ang cap ng Overpass.
        $service = app(PlanoraService::class);
        $labels = $response->json('selected_places');

        // Ang pagkakasunod-sunod ay: prependPlaces() pagkatapos ay
        // allowedPlacesForPrompt(). Ginaya rito ang parehong anyo.
        $overpass = array_map(fn ($i) => "Overpass Spot {$i} (Mall)", range(1, 10));
        $combined = implode('|', array_merge($labels, $overpass));
        $allowed = $service->allowedPlacesForPrompt($combined, $labels);

        foreach ($labels as $label) {
            $this->assertStringContainsString(
                $label,
                $allowed,
                "Pick '{$label}' was dropped from the prompt's allowed-places list."
            );
        }

        // Ang cap ay para sa Overpass lamang — hindi dapat putulin ang mga pinili.
        $this->assertCount(PlanoraService::NEARBY_PLACE_PROMPT_LIMIT, array_filter(
            explode(', ', $allowed),
            fn ($entry) => str_starts_with($entry, 'Overpass Spot')
        ));
    }

    public function test_generating_without_picks_still_works_at_a_valid_budget(): void
    {
        // RETARGETED: noong nangunguna ito ng "zero allowance still generates".
        // Hindi na posible iyon ngayon — may floor na ang MIN_DAILY_ALLOWANCE.
        // Ang layunin ng pagsubok ay nananatili: OPTIONAL ang picker, kaya kahit
        // walang pinili ay buo ang itinerary.
        $hotel = $this->makeHotel();
        $this->makeLocation('SM Center Dagupan', Location::CATEGORY_MALL, 300);
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/generate-plan', [
            'hotel' => $hotel->name,
            'budget' => 5000,
            'days' => 2,
            'selected_places' => [],
        ]);

        // 2 araw, 1 gabi x PHP 1,000 = PHP 1,000, kaya PHP 2,000/day ang allowance.
        $response->assertOk()
            ->assertJsonPath('selected_places', [])
            ->assertJsonPath('daily_allowance', 2000);

        $this->assertSame([], Plan::query()->latest('id')->first()->selected_places);

        // Ang itinerary ay buo pa rin kahit walang pinili — ito ang sinasabi ng
        // "optional" na label sa picker.
        $this->assertNotEmpty($response->json('recommendation'));
        $this->assertStringContainsString('Day 1', $response->json('recommendation'));
    }

    public function test_a_one_peso_budget_is_rejected(): void
    {
        // REGRESSION: eksaktong repro ng "kahit 1 peso lang, gumagana pa din".
        // Isang araw = walang gabi = PHP 0 ang lodging, kaya dating dumaan ito.
        $hotel = $this->makeHotel();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/generate-plan', [
            'hotel' => $hotel->name,
            'budget' => 1,
            'days' => 1,
        ]);

        $response->assertStatus(422)->assertJsonPath('error', 'Budget too low for a real itinerary. After accommodation you have about PHP 1 per day left, but Planora needs at least PHP 200/day — one meal plus tricycle fares. For 1 day(s) that means PHP 200 in total. Please add PHP 199 to your budget, or pick a cheaper stay.');

        $this->assertDatabaseCount('plans', 0);
    }

    public function test_a_budget_that_only_covers_lodging_is_rejected(): void
    {
        // Ang pangalawang daan: 2 araw sa PHP 1,000/night ay PHP 1,000 na
        // lodging. Ang dating guard ay okay lang dito — kaya lamang ang
        // allowance floor na tumutigil sa PHP 0/day na plano.
        $hotel = $this->makeHotel();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/generate-plan', [
            'hotel' => $hotel->name,
            'budget' => 1001,
            'days' => 2,
        ]);

        // Ito ay budget guard, hindi validation — kaya 'error' ang susi, at
        // walang 'errors' bag.
        $response->assertStatus(422)
            ->assertJsonMissingPath('errors')
            ->assertJsonPath('error', fn ($error) => str_contains($error, 'Budget too low'));

        $this->assertDatabaseCount('plans', 0);
    }

    public function test_exactly_the_minimum_allowance_is_accepted(): void
    {
        // Ang boundary ay dapat "kosmetiko": eksaktong PHP 200/day ay tumatanggap,
        // gaya ng rule ng place picker na '==' ay nasa loob ng bracket.
        //
        // PHP 1,000/night x 1 gabi (2 araw - 1) = PHP 1,000 lodging, kaya
        // kailangan ng 1,000 + (200 x 2) = 1,400 para magkaroon ng eksaktong
        // 200/day.
        $hotel = $this->makeHotel();
        $user = User::factory()->create();

        $this->actingAs($user)->postJson('/generate-plan', [
            'hotel' => $hotel->name,
            'budget' => 1000 + (PlanoraService::MIN_DAILY_ALLOWANCE * 2),
            'days' => 2,
        ])->assertOk()->assertJsonPath('daily_allowance', 200);

        // Isang piso lang sa ibaba ng floor ay sapat na para tumanggal.
        $this->actingAs($user)->postJson('/generate-plan', [
            'hotel' => $hotel->name,
            'budget' => 1399,
            'days' => 2,
        ])->assertStatus(422);
    }

    public function test_the_one_day_zero_nights_rule_survives_the_floor(): void
    {
        // REGRESSION GUARD: ang floor ay hindi dapat makasira ang "1 araw = 0
        // gabi" na rule. Sa PHP 1,000/night, ang isang araw ay may ZERO na
        // lodging — kaya ang buong PHP 500 ay allowance, at PHP 500 > floor.
        $hotel = $this->makeHotel();
        $user = User::factory()->create();

        // Ang allowance ay eksaktong 500 — kung may napigil na gabi, magiging
        // 0 o negative ito. Ito ang totoong assertion; walang 'nights' key sa
        // JSON response (tingnan ang PlanTest::...zero_nights... na dating
        // nagtatassert ng 0 laban sa null — isang walang epekto na pagsubok).
        $this->actingAs($user)->postJson('/generate-plan', [
            'hotel' => $hotel->name,
            'budget' => 500,
            'days' => 1,
        ])->assertOk()
            ->assertJsonPath('daily_allowance', 500);
    }

    public function test_regenerating_a_saved_plan_reuses_its_picks(): void
    {
        $hotel = $this->makeHotel();
        $this->makeLocation('SM Center Dagupan', Location::CATEGORY_MALL, 300);
        $user = User::factory()->create();

        $this->actingAs($user)->postJson('/generate-plan', [
            'hotel' => $hotel->name,
            'budget' => 5000,
            'days' => 2,
            'selected_places' => ['SM Center Dagupan'],
        ])->assertOk();

        $plan = Plan::query()->latest('id')->first();

        $this->actingAs($user)->post("/plans/{$plan->id}/regenerate")->assertRedirect();

        // Ang "Regenerate" ay dapat makapagbigay ng ibang bersyon ng parehong
        // plano — pati na ang mga pinili.
        $this->assertStringContainsString('SM Center Dagupan (Mall)', $plan->fresh()->ai_recommendation);
        $this->assertSame(['SM Center Dagupan (Mall)'], $plan->fresh()->selected_places);
    }

    public function test_a_short_pick_cannot_hijack_an_unrelated_catalogue_row(): void
    {
        // REGRESSION: ang fallback substring match ay nagtutugma sa halos lahat
        // ng coastal catalogue. Ang "beach" ay isang substring ng
        // "Tondaligan Blue Beach" — naaabot ang guard na hindi ito eksakto.
        $this->makeLocation('Tondaligan Blue Beach', Location::CATEGORY_BEACH, 100);
        $this->makeLocation('Bonuan Blue Beach', Location::CATEGORY_BEACH, 100);

        $service = app(PlanoraService::class);

        // Maikli at pangkalahatang: dapat itong i-drop, hindi itong mapili sa
        // unang beach na na-pagkatapos.
        $this->assertSame([], $service->selectedPlaceLabels(['beach']));

        // Sapat ang haba para sa typo-tolerance — dapat pa ring mahanap.
        $this->assertSame(
            ['Tondaligan Blue Beach (Beach)'],
            $service->selectedPlaceLabels(['Tondaligan Blue Beac'])
        );
    }

    public function test_the_planner_renders_a_fourth_step_for_the_picker(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/planora')
            ->assertOk()
            ->assertSee('stub-4', false)
            ->assertSee('id="step-3"', false)
            ->assertSee('id="step-4"', false)
            ->assertSee('id="places-list"', false)
            ->assertSee('Pick your places', false)
            ->assertSee('Generate itinerary', false);
    }

    public function test_the_picker_guards_against_the_bugs_found_in_review(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/planora');
        $response->assertOk();

        // 1) Ang "Generate itinerary" ay dinidisable sa submit pero dapat
        //    maibalik sa enabled pagkatapos — kung hindi, patay na ang retry
        //    pagkatapos ng isang error.
        $response->assertSee("toggleButtonState('btn-generate', false);", false);
        $response->assertSee("toggleButtonState('btn-generate', true);", false);

        // 2) Isang pinagmulan ng itinerary text ang ginagamit ng dalawang map
        //    renderer, para hindi mapindutin ang parehong lugar.
        $response->assertSee('function currentItineraryText()', false);
        $response->assertSee('const itineraryText = currentItineraryText();', false);

        // 3) Ibinabahagi ang set ng naka-pin na kategorya, at ang legend ay
        //    na-a-update pagkatapos ng mga pinili (hindi bago).
        $response->assertSee('let plottedMapTypes = new Set();', false);
        $response->assertSee('plottedMapTypes.add(place.category);', false);

        // 4) Ang cache key ay kasama ang hotel — hindi lang ang allowance.
        $response->assertSee('const cacheKey = `${allowance}|${hotelName}', false);

        // 5) Hindi ipinapadala ang map fallback bilang pinagmulan kapag
        //    wala pa sa hotel ang tunay na coordinates.
        $response->assertSee('let selectedHotelGeocoded = false;', false);
        $response->assertSee('selectedHotelGeocoded ? selectedLat', false);
    }

    public function test_step_02_mirrors_the_minimum_allowance_before_the_server_rejects(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/planora');
        $response->assertOk();

        // Ang floor ay ipinapasa sa browser gaya ng nightsOffset, kaya hindi na
        // "looks fine" ang Step 02 at "Budget too low" ang resulta pagkatapos.
        $response->assertSee('data-min-daily-allowance', false);
        $response->assertSee('const MIN_DAILY_ALLOWANCE = Number(plannerConfig.minDailyAllowance)', false);
        $response->assertSee('remaining / safeDays < MIN_DAILY_ALLOWANCE', false);
        $response->assertSee('Planora needs at least', false);
    }

    public function test_a_post_fetch_error_can_no_longer_wipe_the_loaded_list(): void
    {
        // REGRESSION: ang tinatawag na `visiblePlaces` ay walang definition, kaya
        // ang ReferenceError ay napupunta sa catch — na dating NAGPAPAKISA ng
        // `placeOptions` at nagre-render pabalik. Ang bunga: 47 na naka-load na
        // lugar ay naging "0" sa bawat chip at "wala kang mapipili".
        //
        // Ang bug ay hindi lang undefined — ang AMPAYER (ang unconditional na
        // clear sa catch) ang totoong sanhi ng "nawala ang lahat".
        $response = $this->actingAs(User::factory()->create())->get('/planora');
        $response->assertOk();

        // Eksaktong ISANG definition. Ito ang invariant na dapat mag-: bawat
        // call site ay dapat may function na tinatawag. Ang dating bug ay
        // zero definition at isang call.
        $content = $response->getContent();
        $this->assertSame(1, substr_count($content, 'function visiblePlaces()'));

        // Hindi dead code: may call site sa labas ng definition.
        $this->assertGreaterThan(
            substr_count($content, 'function visiblePlaces()'),
            substr_count($content, 'visiblePlaces(')
        );

        // Ang clear sa catch ay CONDITIONAL sa `fetched` — kung dumating na ang
        // payload, hindi na dapat mag-clear kahit may ibang error sa huli.
        $response->assertSee('let fetched = false;', false);
        $response->assertSee('fetched = true;', false);
        $response->assertSee('if (!fetched) {', false);

        // Ang DOM write ay nasa loob na ng try at null-guarded, para hindi na
        // maging unhandled rejection (walang fetch, walang mensahe) kapag
        // nawala ang element sa isang cached na HTML.
        $this->assertSame(1, substr_count($response->getContent(), "document.getElementById('places-allowance')"));
        $response->assertSee('if (allowanceEl) {', false);
        $response->assertSee('if (originEl) {', false);
    }

    public function test_the_empty_state_distinguishes_an_empty_catalogue_from_a_small_budget(): void
    {
        $user = User::factory()->create();

        // Walang naka-seed na lugar → ang mensahe ay dapat humiling ng
        // db:seed, HINDI "wala sa budget mo".
        $emptyCatalogue = $this->actingAs($user)->getJson('/api/places?daily_allowance=5000');
        $emptyCatalogue->assertOk();

        $names = [];
        foreach ($emptyCatalogue->json('within_budget') as $list) {
            foreach ($list as $place) {
                $names[] = $place['name'];
            }
        }
        $this->assertSame([], $names);

        // Sa browser, ito ang siyang sumusuri sa `over_budget`: kapag parehong
        // grupo ay walang laman, katalogo ang problema; kapag may laman ang
        // over_budget, budget ang problema.
        $response = $this->actingAs($user)->get('/planora');
        $response->assertSee('function renderPlaceEmptyState()', false);
        $response->assertSee('php artisan db:seed', false);
        $response->assertSee('function catalogueCount()', false);

        // Kapag may katalogo ngunit walang umaangkop, hindi dapat "db:seed".
        $this->makeLocation('Cheap Mall', Location::CATEGORY_MALL, 100);
        $withData = $this->getJson('/api/places?daily_allowance=1');
        $withData->assertOk();
        $this->assertNotEmpty($withData->json('over_budget.mall'));
    }

    public function test_the_budget_has_an_upper_bound(): void
    {
        // Walang hangganan ang dating validation (`min:1` lamang), kaya napasok
        // ang PHP 123,000,000 na allowance. Sa ganung halaga, lahat ng 47 na
        // lugar ay "swak" — walang saysay ang picker.
        $hotel = $this->makeHotel();
        $user = User::factory()->create();

        $this->actingAs($user)->postJson('/generate-plan', [
            'hotel' => $hotel->name,
            'budget' => PlanoraService::MAX_TRIP_BUDGET + 1,
            'days' => 2,
        ])->assertStatus(422)->assertJsonValidationErrors('budget');

        // Eksaktong sa hangganan ay tinatanggap pa rin.
        $this->actingAs($user)->postJson('/generate-plan', [
            'hotel' => $hotel->name,
            'budget' => PlanoraService::MAX_TRIP_BUDGET,
            'days' => 2,
        ])->assertOk();
    }

    public function test_the_picker_exposes_a_retry_and_a_distinct_error_message(): void
    {
        $response = $this->actingAs(User::factory()->create())->get('/planora');
        $response->assertOk();

        // Walang permanenteng hadlang: may "Try again" na nag-a-appear sa
        // error, at ang "Generate itinerary" ay nananatiling available.
        $response->assertSee('id="places-retry"', false);
        $response->assertSee('loadPlaceOptions(true)', false);
        $response->assertSee('function setPlacesStatus(', false);
        $response->assertSee('You can still generate an itinerary below.', false);
    }

    public function test_refreshing_never_dumps_the_traveller_into_step_02(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/planora');
        $response->assertOk();

        // REGRESSION: noong binabasa ang sessionStorage sa load at tinawag ang
        // selectHotel(), napupunta ang traveller sa Step 02 kahit walang
        // na-restore na budget, days, o rest schedule — kaya "nastuck" doon.
        $response->assertDontSee('consumePendingHotelRestore', false);
        $response->assertDontSee('pendingHotelRestoreIdx', false);
        $response->assertDontSee('function preserveHotelSelection', false);

        // Ang natatanging bakas ng dating gawi ay dapat malinis sa bawat load.
        $response->assertSee("sessionStorage.removeItem('planora_selected_idx');", false);
        $response->assertDontSee("sessionStorage.getItem('planora_selected_idx')", false);
    }

    public function test_a_blank_budget_is_not_treated_as_a_valid_budget(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/planora');
        $response->assertOk();

        // REGRESSION: noong `budget > 0` ang kundisyon ng unang branch ng
        // checkBudget(), napapasok ang BLANK na budget sa 'else' — pindutan
        // bukas at walang "#budget-hint". Ito ang hitsura pagkatapos ng refresh.
        $response->assertSee('const hasBudget = Number.isFinite(budget) && budget > 0;', false);
        $response->assertSee('Enter your total budget to see your daily allowance.', false);

        // Ang 'days' ay dapat may sariling proteksyon: ang NaN ay
        // nase-serialize bilang JSON null at tinutuktok ng server ang
        // 'days' => 'required' bilang 422 na walang malinaw na mensahe.
        $response->assertSee('function readTripDays()', false);
        $response->assertSee('function readTripBudget()', false);
        $response->assertSee('if (days === null) {', false);
    }
}
