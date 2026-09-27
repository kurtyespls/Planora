<?php

namespace Tests\Feature;

use App\Models\Hotel;
use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlanTest extends TestCase
{
    use RefreshDatabase;

    private function makePlanFor(User $user, array $overrides = []): Plan
    {
        return Plan::create(array_merge([
            'user_id' => $user->id,
            'hotel_name' => 'Star Plaza Hotel',
            'budget' => 8000,
            'total_days' => 3,
            'rest_days' => ['Morning'],
            'ai_recommendation' => "### Day 1\n- **8:00 AM:** Breakfast near the hotel.",
            'ai_provider' => 'local',
        ], $overrides));
    }

    private function makeHotel(): Hotel
    {
        return Hotel::create([
            'name' => 'Star Plaza Hotel',
            'image_url' => '/images/dagupan/sm-center.jpg',
            'price' => '2500',
            'rating' => 8.4,
            'lat' => 16.0438,
            'lon' => 120.3331,
        ]);
    }

    public function test_guest_cannot_generate_a_plan(): void
    {
        $this->postJson('/generate-plan', [
            'hotel' => 'Star Plaza Hotel',
            'budget' => 8000,
            'days' => 3,
        ])->assertStatus(401);

        $this->assertDatabaseCount('plans', 0);
    }

    public function test_signed_in_user_generates_a_plan_they_own(): void
    {
        $this->makeHotel();
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/generate-plan', [
            'hotel' => 'Star Plaza Hotel',
            'budget' => 12000,
            'days' => 3,
            'rest_days' => ['Whole Day'],
        ]);

        $response->assertOk()->assertJsonStructure([
            'recommendation',
            'budget_warning',
            'daily_allowance',
            'ai_provider',
        ]);

        $this->assertDatabaseHas('plans', [
            'user_id' => $user->id,
            'hotel_name' => 'Star Plaza Hotel',
            'ai_provider' => 'local',
        ]);
    }

    public function test_whole_day_rest_option_is_accepted_but_unknown_values_are_not(): void
    {
        $this->makeHotel();
        $user = User::factory()->create();

        $this->actingAs($user)->postJson('/generate-plan', [
            'hotel' => 'Star Plaza Hotel',
            'budget' => 12000,
            'days' => 2,
            'rest_days' => ['Whole Day'],
        ])->assertOk();

        $this->actingAs($user)->postJson('/generate-plan', [
            'hotel' => 'Star Plaza Hotel',
            'budget' => 12000,
            'days' => 2,
            'rest_days' => ['Siesta'],
        ])->assertStatus(422);
    }

    public function test_custom_rest_windows_are_accepted_and_saved(): void
    {
        // Deterministic: hindi kailangan ng AI call para ma-verify ang
        // pag-save ng oras ng rest.
        config(['services.groq.key' => null]);

        $this->makeHotel();
        $user = User::factory()->create();

        $this->actingAs($user)->postJson('/generate-plan', [
            'hotel' => 'Star Plaza Hotel',
            'budget' => 12000,
            'days' => 3,
            'rest_days' => ['14:00-16:00', '22:00-06:00'],
        ])->assertOk();

        $plan = Plan::where('user_id', $user->id)->latest('id')->first();

        // Ang storage ay 'HH:MM' pa rin; 12-hour lang ang display label.
        $this->assertSame(['14:00-16:00', '22:00-06:00'], $plan->rest_days);
        $this->assertSame(
            ['2:00 PM–4:00 PM (2h)', '10:00 PM–6:00 AM (8h)'],
            $plan->rest_schedule_labels
        );
    }

    public function test_invalid_or_excessive_rest_windows_are_rejected(): void
    {
        $this->makeHotel();
        $user = User::factory()->create();

        $payload = [
            'hotel' => 'Star Plaza Hotel',
            'budget' => 12000,
            'days' => 3,
        ];

        foreach ([['25:00-26:00'], ['abc'], ['14:00'], ['14:00 - 16:00']] as $invalid) {
            $this->actingAs($user)
                ->postJson('/generate-plan', $payload + ['rest_days' => $invalid])
                ->assertStatus(422);
        }

        // Hanggang 4 na rest period lang ang tinatanggap.
        $this->actingAs($user)->postJson('/generate-plan', $payload + [
            'rest_days' => ['01:00-02:00', '03:00-04:00', '05:00-06:00', '07:00-08:00', '09:00-10:00'],
        ])->assertStatus(422);

        $this->assertDatabaseCount('plans', 0);
    }

    public function test_local_fallback_itinerary_respects_the_chosen_rest_window(): void
    {
        // Walang AI key → built-in planner, na siyang sumusunod sa oras ng rest.
        config(['services.groq.key' => null]);

        $this->makeHotel();
        $user = User::factory()->create();

        $this->actingAs($user)->postJson('/generate-plan', [
            'hotel' => 'Star Plaza Hotel',
            'budget' => 12000,
            'days' => 2,
            'rest_days' => ['14:00-16:00'],
        ])->assertOk();

        $plan = Plan::where('user_id', $user->id)->latest('id')->first();

        $this->assertSame('local', $plan->ai_provider);
        $this->assertStringContainsString('### Rest Schedule (daily)', $plan->ai_recommendation);
        $this->assertStringContainsString('2:00 PM – 4:00 PM', $plan->ai_recommendation);
        // Ang 3:30 PM slot ay nasa loob ng rest window, kaya hindi ito dapat lumabas.
        $this->assertStringNotContainsString('3:30 PM', $plan->ai_recommendation);
    }

    public function test_owner_can_view_their_saved_plan(): void
    {
        $user = User::factory()->create();
        $plan = $this->makePlanFor($user);

        $this->actingAs($user)
            ->get('/plans/' . $plan->id)
            ->assertOk()
            ->assertSee('Star Plaza Hotel')
            ->assertSee('Day 1');
    }

    public function test_another_user_cannot_view_someone_elses_plan(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $plan = $this->makePlanFor($owner);

        $this->actingAs($stranger)
            ->get('/plans/' . $plan->id)
            ->assertRedirect('/planora');
    }

    public function test_owner_can_delete_their_plan(): void
    {
        $user = User::factory()->create();
        $plan = $this->makePlanFor($user);

        $this->actingAs($user)
            ->delete('/plans/' . $plan->id)
            ->assertRedirect('/profile/' . $user->id . '?tab=plans');

        $this->assertDatabaseMissing('plans', ['id' => $plan->id]);
    }

    public function test_another_user_cannot_delete_someone_elses_plan(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $plan = $this->makePlanFor($owner);

        $this->actingAs($stranger)
            ->delete('/plans/' . $plan->id)
            ->assertRedirect('/planora');

        $this->assertDatabaseHas('plans', ['id' => $plan->id]);
    }

    public function test_missing_plan_returns_not_found(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/plans/999999')->assertNotFound();
    }

    public function test_plan_cannot_be_generated_for_nonexistent_hotel(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/generate-plan', [
            'hotel' => 'Non Existent Hotel',
            'budget' => 10000,
            'days' => 2,
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'error' => 'We could not find that stay in our Dagupan listings. Please pick one from the list and try again.',
        ]);
    }

    public function test_one_day_trip_requires_no_overnight_hotel_cost(): void
    {
        $this->makeHotel();
        $user = User::factory()->create();

        // 1 day = 0 nights. A budget of 500 PHP suffices even if hotel is 2500/night.
        $response = $this->actingAs($user)->postJson('/generate-plan', [
            'hotel' => 'Star Plaza Hotel',
            'budget' => 500,
            'days' => 1,
        ]);

        $response->assertOk();
        $this->assertEquals(0, $response->json('nights'));
    }

    public function test_owner_can_rename_their_plan(): void
    {
        $user = User::factory()->create();
        $plan = $this->makePlanFor($user);

        $this->actingAs($user)
            ->patch('/plans/' . $plan->id, ['title' => 'My Dagupan Getaway'])
            ->assertRedirect('/plans/' . $plan->id);

        $this->assertEquals('My Dagupan Getaway', $plan->fresh()->title);
        $this->assertEquals('My Dagupan Getaway', $plan->fresh()->display_title);
    }

    public function test_another_user_cannot_rename_someone_elses_plan(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $plan = $this->makePlanFor($owner, ['title' => 'Original Title']);

        $this->actingAs($stranger)
            ->patch('/plans/' . $plan->id, ['title' => 'Hacked Title'])
            ->assertRedirect('/planora');

        $this->assertEquals('Original Title', $plan->fresh()->title);
    }

    public function test_owner_can_regenerate_their_plan(): void
    {
        $this->makeHotel();
        $user = User::factory()->create();
        $plan = $this->makePlanFor($user, [
            'ai_recommendation' => 'Old recommendation',
        ]);

        $this->actingAs($user)
            ->post('/plans/' . $plan->id . '/regenerate')
            ->assertRedirect('/plans/' . $plan->id);

        $fresh = $plan->fresh();
        $this->assertNotEquals('Old recommendation', $fresh->ai_recommendation);
        $this->assertNotEmpty($fresh->ai_recommendation);
    }

    public function test_another_user_cannot_regenerate_someone_elses_plan(): void
    {
        $owner = User::factory()->create();
        $stranger = User::factory()->create();
        $plan = $this->makePlanFor($owner, [
            'ai_recommendation' => 'Original recommendation',
        ]);

        $this->actingAs($stranger)
            ->post('/plans/' . $plan->id . '/regenerate')
            ->assertRedirect('/planora');

        $this->assertEquals('Original recommendation', $plan->fresh()->ai_recommendation);
    }
}
