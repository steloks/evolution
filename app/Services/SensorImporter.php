<?php

namespace App\Services;

use App\Models\City;
use App\Models\Sensor;
use Illuminate\Database\Eloquent\Collection;

class SensorImporter
{
    public function __construct(private OpenAqClient $openAq) {}

    public function run(): int
    {
        $cities = City::all();
        $sensors = [];

        foreach ($this->openAq->pm25Locations('BG') as $location) {
            $latitude = data_get($location, 'coordinates.latitude');
            $longitude = data_get($location, 'coordinates.longitude');

            if (! is_numeric($latitude) || ! is_numeric($longitude)) {
                continue;
            }

            $city = $this->findCity($cities, (float) $latitude, (float) $longitude);

            if ($city === null) {
                continue;
            }

            foreach (data_get($location, 'sensors', []) as $sensor) {
                if (data_get($sensor, 'parameter.id') !== OpenAqClient::PM25_PARAMETER_ID || ! is_numeric(data_get($sensor, 'id'))) {
                    continue;
                }

                $sensors[] = [
                    'id' => (int) $sensor['id'],
                    'location_name' => (string) data_get($location, 'name', ''),
                    'provider' => (string) data_get($location, 'provider.name', 'unknown'),
                    'latitude' => (float) $latitude,
                    'longitude' => (float) $longitude,
                    'city_id' => $city->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        Sensor::upsert($sensors, ['id'], ['location_name', 'provider', 'latitude', 'longitude', 'city_id', 'updated_at']);

        return count($sensors);
    }

    /** @param  Collection<int, City>  $cities */
    private function findCity(Collection $cities, float $latitude, float $longitude): ?City
    {
        return $cities->first(fn (City $city): bool => $city->distanceKmTo($latitude, $longitude) <= $city->radius_km);
    }
}
