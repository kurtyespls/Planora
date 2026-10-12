<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Guards the admin "Places & Spots" CRUD.
 *
 * The locations table powers both the landing "A City Full of Stories" gallery
 * and the Step 03 picker, so admins must be able to curate it without a reseed.
 * These tests lock in the routing, validation, cache invalidation and the
 * admin-only guard so the public catalogue stays consistent.
 */
class AdminPlacesTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Bonuan Blue Beach',
            'category' => Location::CATEGORY_BEACH,
            'location' => 'Bonuan Binloc, Dagupan City',
            'image_url' => 'https://example.com/beach.jpg',
            'latitude' => 16.0439,
            'longitude' => 120.3411,
            'budgetPerDay' => 500,
            'rating' => 4.6,
            'icon' => 'B',
            'description' => 'A relaxing beach getaway.',
        ], $overrides);
    }

    public function test_guest_is_redirected_away_from_the_places_manager(): void
    {
        $this->get('/admin/places')->assertRedirect('/login');
    }

    public function test_non_admin_cannot_open_the_places_manager(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'user']))
            ->get('/admin/places')
            ->assertForbidden();
    }

    public function test_admin_can_view_the_places_manager_and_its_listing(): void
    {
        Location::create($this->validPayload());

        $response = $this->actingAs($this->admin())->get('/admin/places');

        $response->assertOk();
        $response->assertSee('Places &amp; Spots', false);
        $response->assertSee('Bonuan Blue Beach', false);
    }

    public function test_admin_can_add_a_place(): void
    {
        $response = $this->actingAs($this->admin())
            ->post('/admin/places', $this->validPayload());

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('locations', [
            'name' => 'Bonuan Blue Beach',
            'category' => Location::CATEGORY_BEACH,
        ]);
    }

    public function test_adding_a_place_requires_name_category_and_coordinates(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/places', [
                'name' => '',
                'category' => '',
                'latitude' => '',
                'longitude' => '',
                'budgetPerDay' => '',
            ])
            ->assertSessionHasErrors(['name', 'category', 'latitude', 'longitude', 'budgetPerDay']);

        $this->assertDatabaseCount('locations', 0);
    }

    public function test_adding_a_place_rejects_an_unknown_category(): void
    {
        $this->actingAs($this->admin())
            ->post('/admin/places', $this->validPayload(['category' => 'volcano']))
            ->assertSessionHasErrors('category');

        $this->assertDatabaseCount('locations', 0);
    }

    public function test_admin_can_update_a_place(): void
    {
        $place = Location::create($this->validPayload());

        $this->actingAs($this->admin())
            ->put("/admin/places/{$place->id}", $this->validPayload(['name' => 'Renamed Beach', 'rating' => 4.9]))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('locations', [
            'id' => $place->id,
            'name' => 'Renamed Beach',
        ]);
    }

    public function test_admin_can_delete_a_place(): void
    {
        $place = Location::create($this->validPayload());

        $this->actingAs($this->admin())
            ->delete("/admin/places/{$place->id}")
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('locations', ['id' => $place->id]);
    }
}
