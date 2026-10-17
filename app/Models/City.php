<?php

namespace App\Models;

use Database\Factories\CityFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['slug', 'name', 'latitude', 'longitude', 'radius_km'])]
class City extends Model
{
    /** @use HasFactory<CityFactory> */
    use HasFactory;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'latitude' => 'float',
            'longitude' => 'float',
            'radius_km' => 'integer',
        ];
    }

    /** @return HasMany<Sensor, $this> */
    public function sensors(): HasMany
    {
        return $this->hasMany(Sensor::class);
    }

    public function distanceKmTo(float $latitude, float $longitude): float
    {
        $latitudeDelta = deg2rad($latitude - $this->latitude);
        $longitudeDelta = deg2rad($longitude - $this->longitude);

        $haversine = sin($latitudeDelta / 2) ** 2
            + cos(deg2rad($this->latitude)) * cos(deg2rad($latitude)) * sin($longitudeDelta / 2) ** 2;

        return 6371 * 2 * asin(sqrt($haversine));
    }
}
