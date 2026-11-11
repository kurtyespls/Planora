<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_dashboard(): void
    {
        $user = User::factory()->create([
            'name' => 'Kurt Tester',
            'points' => 150,
            'level' => 2,
        ]);

        $myPlan = Plan::create([
            'user_id' => $user->id,
            'title' => 'My Dagupan Weekend Tour',
            'hotel_name' => 'Star Plaza Hotel',
            'budget' => 4500.00,
            'total_days' => 2,
            'is_public' => true,
        ]);

        $otherUser = User::factory()->create(['name' => 'Maria Traveler']);
        $communityPlan = Plan::create([
            'user_id' => $otherUser->id,
            'title' => 'Bonuan Beach Holiday',
            'hotel_name' => 'Lenox Hotel',
            'budget' => 6000.00,
            'total_days' => 3,
            'is_public' => true,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Welcome back, Kurt Tester');
        $response->assertSee('My Dagupan Weekend Tour');
        $response->assertSee('Bonuan Beach Holiday');
        $response->assertSee('Star Plaza Hotel');
        $response->assertSee('Explorer');
        $response->assertSee('150 XP');
    }

    public function test_user_can_view_public_plans_catalogue(): void
    {
        $author = User::factory()->create(['name' => 'Sara Explorer']);
        $publicPlan = Plan::create([
            'user_id' => $author->id,
            'title' => 'Tondaligan Sun and Seafood',
            'hotel_name' => 'Lenox Hotel',
            'budget' => 5000,
            'total_days' => 2,
            'is_public' => true,
            'ai_recommendation' => 'Day 1: Morning beach walk and Dagupan bangus lunch.',
        ]);

        $response = $this->get('/public-plans');
        $response->assertStatus(200);
        $response->assertSee('Tondaligan Sun and Seafood');
        $response->assertSee('Lenox Hotel');

        $detailResponse = $this->get('/public-plans/' . $publicPlan->id);
        $detailResponse->assertStatus(200);
        $detailResponse->assertSee('Tondaligan Sun and Seafood');
        $detailResponse->assertSee('Sara Explorer');
        $detailResponse->assertSee('Day 1: Morning beach walk');
    }

    public function test_user_can_view_profile_page(): void
    {
        $user = User::factory()->create([
            'name' => 'Alex Cruiser',
            'points' => 320,
            'level' => 3,
        ]);

        $response = $this->actingAs($user)->get('/profile/' . $user->id);
        $response->assertStatus(200);
        $response->assertSee('Alex Cruiser');
        $response->assertSee('Level 3');
        $response->assertSee('320 XP');
    }
}
