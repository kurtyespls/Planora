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

    public function test_step_three_route_card_has_distance_eta_navigation_and_hotel_focus(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get('/planora');

        $response->assertOk();

        // Ang card ay nasa markup ng Step 3 bago ang mapa.
        $response->assertSee('id="route-guidance-card"', false);
        $response->assertSee('id="route-guidance-details"', false);
        $response->assertSee('id="route-badge-live"', false);

        // Distansya + ETA mula sa routesfound, direct Google Maps navigation,
        // at hotel focus button.
        $response->assertSee('Estimated driving time', false);
        $response->assertSee('to <em>${hotelName}</em>', false);
        $response->assertSee('Navigate (Google Maps) ↗', false);
        $response->assertSee('https://www.google.com/maps/dir/?api=1&origin=', false);
        $response->assertSee('focusOnHotel()', false);
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

    public function test_saved_plan_view_offers_route_from_my_location_and_direct_navigation(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        $this->makeHotel();
        $plan = $this->makePlanFor($user);

        $response = $this->actingAs($user)->get('/plans/' . $plan->id);

        $response->assertOk();

        // Button para sa GPS routing at direct na "Navigate" link sa hotel.
        $response->assertSee('Route from my location', false);
        $response->assertSee('id="btn-detect-route-show"', false);
        $response->assertSee('id="show-google-maps-link"', false);
        $response->assertSee('https://www.google.com/maps/dir/?api=1&destination=', false);

        // Geolocation + route drawing sa loob ng saved plan na mapa.
        $response->assertSee('navigator.geolocation.getCurrentPosition', false);
        $response->assertSee("showRouteControl.on('routesfound'", false);
        $response->assertSee('id="show-route-info"', false);
        $response->assertSee('describeFallbackRoute', false);
    }
}
