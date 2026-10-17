<?php

namespace Tests\Feature;

use App\Models\Measurement;
use App\Services\Metrics;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HealthAndMetricsTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_is_ok_when_the_data_is_fresh(): void
    {
        $this->travelTo('2026-10-17 12:30:00');
        Measurement::factory()->create(['hour' => '2026-10-17 10:00:00']);

        $this->getJson('/api/health')
            ->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('latest_measurement', '2026-10-17T10:00:00Z');
    }

    public function test_health_is_stale_when_the_imports_stopped(): void
    {
        $this->travelTo('2026-10-17 12:30:00');
        Measurement::factory()->create(['hour' => '2026-10-16 10:00:00']);

        $this->getJson('/api/health')->assertOk()->assertJsonPath('status', 'stale');
    }

    public function test_health_is_down_without_a_database(): void
    {
        DB::connection()->getSchemaBuilder()->drop('measurements');

        $this->getJson('/api/health')->assertServiceUnavailable()->assertJsonPath('database', 'down');
    }

    public function test_metrics_include_the_last_import_and_ranking_requests(): void
    {
        Measurement::factory()->count(3)->create();
        app(Metrics::class)->recordImport(stored: 42, failedSensors: 1, durationSeconds: 12.34);

        $this->getJson('/api/rankings?from=2026-07-01T00:00&to=2026-07-02T00:00');

        $this->get('/api/metrics')
            ->assertOk()
            ->assertSee('clean_air_measurements 3')
            ->assertSee('clean_air_last_import_stored 42')
            ->assertSee('clean_air_last_import_failed_sensors 1')
            ->assertSee('clean_air_ranking_requests_total 1');
    }
}
