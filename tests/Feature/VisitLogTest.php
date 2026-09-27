<?php

namespace Tests\Feature;

use App\Models\Location;
use App\Models\User;
use App\Models\VisitLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VisitLogTest extends TestCase
{
    use RefreshDatabase;

    private function makeLocation(): Location
    {
        return Location::create([
            'name' => 'SM Center Dagupan',
            'location' => 'A.B. Fernandez Avenue, Downtown District, Dagupan City',
            'latitude' => 16.0440100,
            'longitude' => 120.3376800,
            'rating' => 8.5,
            'category' => Location::CATEGORY_MALL,
            'icon' => 'M',
            'budgetPerDay' => 200,
            'description' => 'The largest shopping mall in Dagupan.',
        ]);
    }

    public function test_tourist_spots_endpoint_returns_seeded_locations(): void
    {
        $location = $this->makeLocation();

        $this->getJson('/api/tourist-spots?q=' . urlencode('sm center'))
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonFragment([
                'id' => $location->id,
                'name' => 'SM Center Dagupan',
                'category' => Location::CATEGORY_MALL,
            ]);
    }

    public function test_tourist_spots_endpoint_can_filter_by_category(): void
    {
        $this->makeLocation();

        $this->getJson('/api/tourist-spots?category=' . Location::CATEGORY_BEACH)
            ->assertOk()
            ->assertJsonCount(0);
    }

    public function test_check_in_records_a_visit_for_a_curated_place(): void
    {
        $user = User::factory()->create();
        $location = $this->makeLocation();

        $this->actingAs($user)->postJson('/api/visit-log/checkin', [
            'spot_id' => $location->id,
            'spot_name' => $location->name,
        ])->assertOk()->assertJson(['success' => true]);

        $this->assertDatabaseHas('visit_logs', [
            'user_id' => $user->id,
            'location_id' => $location->id,
            'location_name' => 'SM Center Dagupan',
        ]);
    }

    public function test_check_in_records_a_visit_for_a_place_without_a_curated_row(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson('/api/visit-log/checkin', [
            'spot_name' => 'Overpass Only Eatery',
        ])->assertOk();

        $this->assertDatabaseHas('visit_logs', [
            'user_id' => $user->id,
            'location_id' => null,
            'location_name' => 'Overpass Only Eatery',
        ]);
    }

    public function test_duplicate_check_in_is_rejected(): void
    {
        $user = User::factory()->create();
        $location = $this->makeLocation();

        $payload = ['spot_id' => $location->id, 'spot_name' => $location->name];

        $this->actingAs($user)->postJson('/api/visit-log/checkin', $payload)->assertOk();
        $this->actingAs($user)->postJson('/api/visit-log/checkin', $payload)->assertStatus(422);

        $this->assertSame(1, VisitLog::count());
    }

    public function test_check_out_computes_the_visit_duration(): void
    {
        $user = User::factory()->create();
        $location = $this->makeLocation();

        $this->actingAs($user)->postJson('/api/visit-log/checkin', [
            'spot_id' => $location->id,
            'spot_name' => $location->name,
        ])->assertOk();

        // Backdate the check-in so the expected duration is deterministic.
        $visit = VisitLog::firstOrFail();
        $visit->checked_in_at = now()->subMinutes(45);
        $visit->save();

        $this->actingAs($user)->postJson('/api/visit-log/checkout', [
            'spot_id' => $location->id,
            'spot_name' => $location->name,
        ])->assertOk()->assertJson(['success' => true, 'duration_minutes' => 45]);

        $this->assertDatabaseHas('visit_logs', [
            'id' => $visit->id,
            'duration_minutes' => 45,
        ]);
    }

    public function test_check_out_without_a_check_in_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson('/api/visit-log/checkout', [
            'spot_name' => 'SM Center Dagupan',
        ])->assertStatus(422);
    }

    public function test_guest_cannot_check_in(): void
    {
        $this->postJson('/api/visit-log/checkin', [
            'spot_name' => 'SM Center Dagupan',
        ])->assertStatus(401);

        $this->assertSame(0, VisitLog::count());
    }
}
