<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Guards the admin panel's "View Live App" / "Visit App" links.
 *
 * Both point at /planora. That route used to bounce every admin straight back
 * to /admin/hotels, so the links were dead ends: an admin could never look at
 * the public app while signed in.
 */
class AdminLiveAppTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_landing_page_still_redirects_to_the_admin_panel(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/')->assertRedirect('/admin/hotels');
    }

    public function test_admin_can_open_the_live_app(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/planora');

        $response->assertOk();
        $response->assertSee('id="hotel-list"', false);
        $response->assertSee('My Profile', false);

        // Admin-only way back to the control center: one link in the top nav
        // and one inside the profile slide panel.
        $response->assertSee('Admin Panel', false);
        $this->assertSame(
            2,
            substr_count($response->getContent(), 'href="/admin/hotels"'),
            'The admin link should appear in both the top nav and the profile panel.'
        );
    }

    public function test_signed_in_tourist_still_reaches_the_planner(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get('/planora');

        $response->assertOk();
        $response->assertSee('id="hotel-list"', false);

        // Tourists must never see the admin shortcut.
        $response->assertDontSee('Admin Panel', false);
        $response->assertDontSee('href="/admin/hotels"', false);
    }

    public function test_guests_still_get_the_marketing_page(): void
    {
        $title = 'Planora — Travel beautifully';

        $this->get('/')->assertOk()->assertSee($title, false)->assertDontSee('Admin Panel', false);
        $this->get('/planora')->assertOk()->assertSee($title, false)->assertDontSee('Admin Panel', false);
    }
}
