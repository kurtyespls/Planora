<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\PlanoraService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Ang Rest schedule sa Trip details (Step 2) ay hindi na fixed na preset chips:
 * ang traveller mismo ang naglalagay ng oras ng pahinga ("pagdating sa hotel"),
 * at ang presets ay quick-fill lang na pwedeng i-edit.
 */
class TripDetailsRestScheduleTest extends TestCase
{
    use RefreshDatabase;

    public function test_trip_details_renders_an_editable_rest_schedule(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get('/planora');

        $response->assertOk();

        // Ang container, add button at live summary ay nasa markup.
        $response->assertSee('id="rest-windows"', false);
        $response->assertSee('id="btn-add-rest"', false);
        $response->assertSee('id="rest-summary"', false);

        // Ang native na time inputs ang pinagmulan ng oras.
        $response->assertSee('type="time"', false);

        // Wala nang fixed na checkbox chips.
        $response->assertDontSee('rest-checkbox', false);
    }

    public function test_rest_presets_come_from_the_service(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get('/planora');

        $response->assertOk();
        $response->assertSee('data-rest-preset', false);

        foreach (PlanoraService::REST_WINDOWS as $label => $window) {
            $response->assertSee('data-start="' . $window[0] . '"', false);
            $response->assertSee('data-end="' . $window[1] . '"', false);
        }

        // Ang dating chronotype label ay pinalitan na ng literal na window.
        $response->assertSee('Evening', false);
        $response->assertDontSee('Night Shift', false);
    }
}
