<?php

namespace Tests\Feature;

use App\Models\City;
use App\Models\Measurement;
use App\Models\Sensor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RankingTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_ranks_cities_by_average_pm25(): void
    {
        $sofia = Sensor::factory()->for(City::factory()->state(['name' => 'Sofia']))->create();
        $sofiaSecond = Sensor::factory()->for($sofia->city)->create();
        $varna = Sensor::factory()->for(City::factory()->state(['name' => 'Varna']))->create();

        Measurement::factory()->for($sofia)->create(['hour' => '2026-07-01 00:00:00', 'pm25' => 10]);
        Measurement::factory()->for($sofiaSecond)->create(['hour' => '2026-07-01 01:00:00', 'pm25' => 20]);
        Measurement::factory()->for($varna)->create(['hour' => '2026-07-01 00:00:00', 'pm25' => 5]);

        $this->getJson('/api/rankings?from=2026-07-01T03:00&to=2026-07-01T05:00')
            ->assertOk()
            ->assertJsonPath('hours', 2)
            ->assertJsonPath('cities', [
                ['name' => 'Varna', 'pm25' => 5, 'sensors' => 1, 'coverage' => 50],
                ['name' => 'Sofia', 'pm25' => 15, 'sensors' => 2, 'coverage' => 100],
            ]);
    }

    public function test_the_period_is_bulgarian_time_and_the_end_hour_is_excluded(): void
    {
        Measurement::factory()->create(['hour' => '2026-07-01 00:00:00']);

        $this->getJson('/api/rankings?from=2026-07-01T03:00&to=2026-07-01T04:00')->assertJsonCount(1, 'cities');
        $this->getJson('/api/rankings?from=2026-07-01T00:00&to=2026-07-01T03:00')->assertJsonCount(0, 'cities');
    }

    public function test_a_period_without_measurements_returns_no_cities(): void
    {
        $this->getJson('/api/rankings?from=2025-01-01T00:00&to=2025-01-02T00:00')
            ->assertOk()
            ->assertJsonPath('cities', []);
    }

    /** @return array<string, array{string, string}> */
    public static function invalidPeriods(): array
    {
        return [
            'missing from' => ['to=2026-07-01T05:00', 'from'],
            'missing to' => ['from=2026-07-01T05:00', 'to'],
            'not a whole hour' => ['from=2026-07-01T03:30&to=2026-07-01T05:00', 'from'],
            'not a date' => ['from=yesterday&to=2026-07-01T05:00', 'from'],
            'to before from' => ['from=2026-07-01T05:00&to=2026-07-01T03:00', 'to'],
            'empty period' => ['from=2026-07-01T05:00&to=2026-07-01T05:00', 'to'],
        ];
    }

    #[DataProvider('invalidPeriods')]
    public function test_it_rejects_invalid_periods(string $query, string $field): void
    {
        $this->getJson("/api/rankings?{$query}")
            ->assertUnprocessable()
            ->assertJsonValidationErrors($field);
    }
}
