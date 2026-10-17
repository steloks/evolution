<?php

namespace Tests\Feature;

use App\Models\Measurement;
use App\Models\Sensor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;
use Tests\TestCase;

class ImportMeasurementsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_one_failing_sensor_does_not_stop_the_others(): void
    {
        Sleep::fake();
        $this->travelTo('2026-10-17 12:30:00');
        Sensor::factory()->create(['id' => 1, 'imported_until' => '2026-10-17 11:00:00']);
        Sensor::factory()->create(['id' => 2, 'imported_until' => '2026-10-17 11:00:00']);

        Http::fake([
            '*/sensors/1/*' => Http::response('down', 500),
            '*/sensors/2/*' => Http::response(['results' => [
                ['value' => 9, 'period' => ['datetimeFrom' => ['utc' => '2026-10-17T11:00:00Z']]],
            ]]),
        ]);

        $this->artisan('app:import-measurements')
            ->expectsOutput('Stored 1 measurements, 1 sensors failed.')
            ->assertFailed();

        $this->assertSame([2], Measurement::pluck('sensor_id')->all());
    }
}
