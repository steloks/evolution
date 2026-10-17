<?php

namespace Database\Factories;

use App\Models\City;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<City> */
class CityFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        $name = fake()->unique()->city();

        return [
            'slug' => Str::slug($name),
            'name' => $name,
            'latitude' => fake()->latitude(41.2, 44.2),
            'longitude' => fake()->longitude(22.4, 28.6),
            'radius_km' => fake()->numberBetween(4, 12),
        ];
    }
}
