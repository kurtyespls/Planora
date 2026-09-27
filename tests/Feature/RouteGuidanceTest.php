<?php

namespace Tests\Feature;

use App\Models\Hotel;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Ang GPS route guidance (Step 3 na mapa at ang saved plan view) ay client-side
 * JS, kaya ang tinitiyak dito ay ang wiring ng markup: awtomatikong geolocation
 * pagpasok sa Step 3, ang route card na may distansya/ETA, ang direct na Google
 * Maps navigation, at ang fallback kapag hindi umubra ang OSRM router.
 */
class RouteGuidanceTest extends TestCase
{
    use RefreshDatabase;

    private function makeHotel(): Hotel
    {
        return Hotel::create([
            'name' => 'Star Plaza Hotel',
            'image_url' => '/images/dagupan/sm-center.jpg',
            'price' => '2500',
            'rating' => 8.4,
            'lat' => 16.0438,
            'lon' => 120.3331,
            'address' => 'Dagupan City, Pangasinan',
        ]);
    }

    private function makePlanFor(User $user): Plan
    {
        return Plan::create([
            'user_id' => $user->id,
            'hotel_name' => 'Star Plaza Hotel',
            'budget' => 8000,
            'total_days' => 3,
            'rest_days' => ['Morning'],
            'ai_recommendation' => "### Day 1\n- **8:00 AM:** Breakfast near the hotel.",
            'ai_provider' => 'local',
        ]);
    }

    public function test_step_three_requests_gps_and_routes_to_the_hotel_automatically(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get('/planora');

        $response->assertOk();

        // Awtomatiko itong tumatakbo papasok sa Step 3 (initMap -> autoDetectAndRouteToHotel).
        $response->assertSee('navigator.geolocation.getCurrentPosition', false);
        $response->assertSee('autoDetectAndRouteToHotel();', false);

        // User pin ('U') at hotel marker ('H') para sa route endpoints.
        $response->assertSee("makeIcon('U', 'pin-user')", false);
        $response->assertSee("makeIcon('H', 'pin-hotel')", false);

        // OSRM (Leaflet Routing Machine) ang gumuguhit ng driving route, at
        // fitBounds ang nag-a-adjust ng view sa buong biyahe.
        $response->assertSee('leaflet-routing-machine', false);
        $response->assertSee("currentRouteControl.on('routesfound'", false);
        $response->assertSee('map.fitBounds(bounds, { padding: [60, 60] });', false);
    }

    public function test_step_three_route_card_shows_distance_and_eta_without_navigation_shortcuts(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get('/planora');

        $response->assertOk();

        // Ang card ay nasa markup ng Step 3 bago ang mapa.
        $response->assertSee('id="route-guidance-card"', false);
        $response->assertSee('id="route-guidance-details"', false);
        $response->assertSee('id="route-badge-live"', false);

        // Distansya + ETA mula sa routesfound pa rin ang ipinapakita ng card.
        $response->assertSee('Estimated driving time', false);
        $response->assertSee('to <em>${hotelName}</em>', false);

        // Ang dating `Navigate (Google Maps) ↗` link at `Hotel` focus button ay
        // tinanggal na, kasama ang JS na humahawak sa kanila.
        $response->assertDontSee('btn-open-external-maps', false);
        $response->assertDontSee('focusOnHotel', false);
        $response->assertDontSee('Navigate (Google Maps)', false);
    }

    public function test_step_three_map_only_plots_places_named_in_the_itinerary(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get('/planora');

        $response->assertOk();

        // Ang fetch ay nag-iimbak lang ng POI descriptors — walang marker na
        // idinadagdag bago pa dumating ang itinerary.
        $response->assertSee('nearbyPlacesIndex.push({', false);
        $response->assertDontSee('.addTo(markerLayers).bindPopup(popupHTML)', false);

        // Ang itinerary text ang tinitignan bago maglagay ng pin, at may
        // normalization/stopword heuristic para hindi tumugma ang generic na
        // salita (hal. "hotel", "beach", "Dagupan").
        $response->assertSee('function renderSuggestedPlaces()', false);
        $response->assertSee('renderSuggestedPlaces();', false);
        $response->assertSee('function itineraryMentionsPlace(', false);
        $response->assertSee('PLACE_STOPWORDS', false);

        // Ang legend ay may tag sa bawat category, at itinatago ang walang pin.
        $response->assertSee('id="map-legend"', false);
        $response->assertSee('data-legend="restaurant"', false);
        $response->assertSee('data-legend="mall"', false);
        $response->assertSee('data-legend="tourist"', false);
        $response->assertSee('data-legend="beach"', false);
        $response->assertSee('function updateLegendVisibility(', false);
    }

    public function test_routing_and_location_failures_are_visible_to_the_traveller(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get('/planora');

        $response->assertOk();

        // Kapag nag-error o hindi sumagot ang router, may straight-line na tantya
        // imbes na manatiling nakatago ang card sa "Calculating...".
        $response->assertSee("currentRouteControl.on('routingerror'", false);
        $response->assertSee('showFallbackEstimate', false);
        $response->assertSee('straight-line', false);

        // Kapag tinanggihan ang GPS permission, may paliwanag pa rin.
        $response->assertSee('Location unavailable', false);
    }

    public function test_saved_plan_view_routes_from_my_location_without_external_navigation(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->makeHotel();
        $plan = $this->makePlanFor($user);

        $response = $this->actingAs($user)->get('/plans/' . $plan->id);

        $response->assertOk();

        // Button para sa GPS routing; ang dating "Navigate ↗" link sa hotel ay
        // tinanggal na kasama ang JS na nag-a-update ng href nito.
        $response->assertSee('Route from my location', false);
        $response->assertSee('id="btn-detect-route-show"', false);
        $response->assertDontSee('show-google-maps-link', false);
        $response->assertDontSee('https://www.google.com/maps/dir/?api=1&destination=', false);

        // Geolocation + route drawing sa loob ng saved plan na mapa.
        $response->assertSee('navigator.geolocation.getCurrentPosition', false);
        $response->assertSee("showRouteControl.on('routesfound'", false);
        $response->assertSee('id="show-route-info"', false);
        $response->assertSee('describeFallbackRoute', false);
    }
}
