<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPurposePagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_can_read_mission_and_follow_vision_and_registration_links(): void
    {
        $this->get('/mission')->assertOk()
            ->assertSee('More opportunity.')
            ->assertSee('Less distance.')
            ->assertSee(route('vision'))
            ->assertSee(route('register'))
            ->assertSee('images/backgrounds/cambodia-platform.webp')
            ->assertSee('images/icons/jobs-blue.svg');
    }

    public function test_vision_has_distinct_content_and_a_link_back_to_mission(): void
    {
        $this->get('/vision')->assertOk()
            ->assertSee('A connected Cambodia.')
            ->assertSee('A shared future.')
            ->assertSee(route('mission'))
            ->assertSee('images/backgrounds/business-directory.webp')
            ->assertSee('images/backgrounds/education.webp');
    }

    public function test_members_see_dashboard_and_account_links_instead_of_registration(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/mission')->assertOk()
            ->assertSee(route('dashboard'))
            ->assertSee(route('account.edit'))
            ->assertDontSee(route('register'));
    }
}
