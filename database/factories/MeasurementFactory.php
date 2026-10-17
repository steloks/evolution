<?php

namespace Database\Factories;

use App\Models\Measurement;
use App\Models\Sensor;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Measurement> */
class MeasurementFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'sensor_id' => Sensor::factory(),
            'hour' => now()->subHours(fake()->numberBetween(1, 24))->startOfHour(),
            'pm25' => fake()->randomFloat(2, 1, 80),
        ];
    }
}
