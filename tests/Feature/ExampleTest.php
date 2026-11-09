<?php

namespace Tests\Feature;

use Database\Seeders\CmsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $this->seed(CmsSeeder::class);

        $response = $this->get('/');

        $response->assertOk()->assertSee('Your everyday Cambodia.');
    }

    public function test_an_empty_cms_shows_a_friendly_placeholder(): void
    {
        $this->get('/')->assertOk()->assertSee('Fresh content is on its way');
    }
}
