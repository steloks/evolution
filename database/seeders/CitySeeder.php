<?php

namespace Database\Seeders;

use App\Models\City;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    /** @var list<array{slug: string, name: string, latitude: float, longitude: float, radius_km: int}> */
    private const array CITIES = [
        ['slug' => 'sofia', 'name' => 'София', 'latitude' => 42.6975, 'longitude' => 23.3242, 'radius_km' => 12],
        ['slug' => 'plovdiv', 'name' => 'Пловдив', 'latitude' => 42.1539, 'longitude' => 24.7500, 'radius_km' => 8],
        ['slug' => 'varna', 'name' => 'Варна', 'latitude' => 43.2191, 'longitude' => 27.9102, 'radius_km' => 8],
        ['slug' => 'burgas', 'name' => 'Бургас', 'latitude' => 42.5065, 'longitude' => 27.4689, 'radius_km' => 8],
        ['slug' => 'stara-zagora', 'name' => 'Стара Загора', 'latitude' => 42.4321, 'longitude' => 25.6426, 'radius_km' => 6],
        ['slug' => 'ruse', 'name' => 'Русе', 'latitude' => 43.8487, 'longitude' => 25.9534, 'radius_km' => 6],
        ['slug' => 'pleven', 'name' => 'Плевен', 'latitude' => 43.4179, 'longitude' => 24.6167, 'radius_km' => 4],
        ['slug' => 'sliven', 'name' => 'Сливен', 'latitude' => 42.6861, 'longitude' => 26.3256, 'radius_km' => 4],
        ['slug' => 'pernik', 'name' => 'Перник', 'latitude' => 42.5967, 'longitude' => 23.0332, 'radius_km' => 4],
        ['slug' => 'shumen', 'name' => 'Шумен', 'latitude' => 43.2706, 'longitude' => 26.9229, 'radius_km' => 4],
        ['slug' => 'dobrich', 'name' => 'Добрич', 'latitude' => 43.5649, 'longitude' => 27.8314, 'radius_km' => 4],
        ['slug' => 'blagoevgrad', 'name' => 'Благоевград', 'latitude' => 42.0146, 'longitude' => 23.0980, 'radius_km' => 4],
        ['slug' => 'haskovo', 'name' => 'Хасково', 'latitude' => 41.9342, 'longitude' => 25.5556, 'radius_km' => 4],
        ['slug' => 'yambol', 'name' => 'Ямбол', 'latitude' => 42.4824, 'longitude' => 26.5001, 'radius_km' => 4],
        ['slug' => 'veliko-tarnovo', 'name' => 'Велико Търново', 'latitude' => 43.0812, 'longitude' => 25.6290, 'radius_km' => 4],
        ['slug' => 'pazardzhik', 'name' => 'Пазарджик', 'latitude' => 42.1993, 'longitude' => 24.3332, 'radius_km' => 4],
        ['slug' => 'vratsa', 'name' => 'Враца', 'latitude' => 43.2105, 'longitude' => 23.5631, 'radius_km' => 4],
        ['slug' => 'gabrovo', 'name' => 'Габрово', 'latitude' => 42.8742, 'longitude' => 25.3182, 'radius_km' => 4],
        ['slug' => 'montana', 'name' => 'Монтана', 'latitude' => 43.4129, 'longitude' => 23.2217, 'radius_km' => 4],
        ['slug' => 'kyustendil', 'name' => 'Кюстендил', 'latitude' => 42.2831, 'longitude' => 22.6922, 'radius_km' => 4],
        ['slug' => 'kardzhali', 'name' => 'Кърджали', 'latitude' => 41.6450, 'longitude' => 25.3658, 'radius_km' => 4],
        ['slug' => 'vidin', 'name' => 'Видин', 'latitude' => 43.9916, 'longitude' => 22.8824, 'radius_km' => 4],
        ['slug' => 'targovishte', 'name' => 'Търговище', 'latitude' => 43.2512, 'longitude' => 26.5722, 'radius_km' => 4],
        ['slug' => 'smolyan', 'name' => 'Смолян', 'latitude' => 41.5744, 'longitude' => 24.7120, 'radius_km' => 4],
        ['slug' => 'silistra', 'name' => 'Силистра', 'latitude' => 44.1171, 'longitude' => 27.2606, 'radius_km' => 4],
        ['slug' => 'razgrad', 'name' => 'Разград', 'latitude' => 43.5333, 'longitude' => 26.5185, 'radius_km' => 4],
        ['slug' => 'lovech', 'name' => 'Ловеч', 'latitude' => 43.1323, 'longitude' => 24.7176, 'radius_km' => 4],
    ];

    public function run(): void
    {
        City::upsert(self::CITIES, ['slug'], ['name', 'latitude', 'longitude', 'radius_km']);
    }
}
