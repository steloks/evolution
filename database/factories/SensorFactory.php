<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\Sensor;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Sensor> */
class SensorFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'id' => fake()->unique()->numberBetween(1, 10_000_000),
            'location_name' => fake()->streetName(),
            'provider' => fake()->randomElement(['EEA', 'AirGradient', 'senstate']),
            'latitude' => fake()->latitude(41.2, 44.2),
            'longitude' => fake()->longitude(22.4, 28.6),
            'city_id' => City::factory(),
        ];
    }
}
