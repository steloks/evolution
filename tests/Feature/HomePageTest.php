<?php

namespace Tests\Feature;

use App\Models\Measurement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_it_passes_the_available_days_in_bulgarian_time(): void
    {
        Measurement::factory()->create(['hour' => '2026-04-17 21:00:00']);
        Measurement::factory()->create(['hour' => '2026-10-17 20:00:00']);

        $this->get('/')
            ->assertOk()
            ->assertSee('data-first-day="2026/04/18"', false)
            ->assertSee('data-last-day="2026/10/17"', false);
    }

    public function test_it_works_without_data(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('data-first-day=""', false);
    }
}
