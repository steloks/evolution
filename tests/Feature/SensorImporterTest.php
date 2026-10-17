<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Sensor;
use App\Services\SensorImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;
use Tests\TestCase;

class SensorImporterTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_assigns_pm25_sensors_to_the_city_they_are_in(): void
    {
        Sleep::fake();
        $sofia = City::factory()->create(['latitude' => 42.6977, 'longitude' => 23.3219, 'radius_km' => 12]);

        Http::fake(['*/locations*' => Http::response(['results' => [
            $this->location(42.70, 23.33, [['id' => 1, 'parameter' => ['id' => 2]], ['id' => 2, 'parameter' => ['id' => 1]]]),
            $this->location(43.21, 27.91, [['id' => 3, 'parameter' => ['id' => 2]]]),
            $this->location(null, null, [['id' => 4, 'parameter' => ['id' => 2]]]),
        ]])]);

        $synced = app(SensorImporter::class)->run();

        $this->assertSame(1, $synced);
        $this->assertSame([1], Sensor::pluck('id')->all());
        $this->assertTrue(Sensor::find(1)->city->is($sofia));
    }

    /**
     * @param  list<array<string, mixed>>  $sensors
     * @return array<string, mixed>
     */
    private function location(?float $latitude, ?float $longitude, array $sensors): array
    {
        return [
            'name' => 'Station',
            'provider' => ['name' => 'EEA'],
            'coordinates' => ['latitude' => $latitude, 'longitude' => $longitude],
            'sensors' => $sensors,
        ];
    }
}
