<?php

namespace Tests\Feature;

use App\Models\LoginHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_members_see_an_empty_state_for_sign_in_activity(): void
    {
        $this->actingAs(User::factory()->create())->get('/dashboard')->assertOk()
            ->assertSee('No sign-in activity yet')
            ->assertDontSee('<table', false);
    }

    public function test_recent_sign_ins_are_listed_with_their_status(): void
    {
        $user = User::factory()->create();
        LoginHistory::factory()->for($user)->create(['ip_address' => '203.0.113.7']);
        LoginHistory::factory()->for($user)->failed()->create(['ip_address' => '198.51.100.4']);
        LoginHistory::factory()->create(['ip_address' => '192.0.2.99']);

        $this->actingAs($user)->get('/dashboard')->assertOk()
            ->assertSee('203.0.113.7')
            ->assertSee('198.51.100.4')
            ->assertDontSee('192.0.2.99')
            ->assertSee('Successful')
            ->assertSee('Failed')
            ->assertDontSee('No sign-in activity yet');
    }
}
