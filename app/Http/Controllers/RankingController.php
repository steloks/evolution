<?php

namespace App\Http\Controllers;

use App\Http\Requests\RankingRequest;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class RankingController extends Controller
{
    public const string TIMEZONE = 'Europe/Sofia';

    public function __invoke(RankingRequest $request): JsonResponse
    {
        $from = CarbonImmutable::createFromFormat(RankingRequest::FORMAT, $request->validated('from'), self::TIMEZONE)->utc();
        $to = CarbonImmutable::createFromFormat(RankingRequest::FORMAT, $request->validated('to'), self::TIMEZONE)->utc();
        $hours = (int) $from->diffInHours($to);

        $startedAt = hrtime(true);

        $cities = DB::table('measurements')
            ->join('sensors', 'sensors.id', '=', 'measurements.sensor_id')
            ->join('cities', 'cities.id', '=', 'sensors.city_id')
            ->where('measurements.hour', '>=', $from)
            ->where('measurements.hour', '<', $to)
            ->groupBy('cities.id', 'cities.name')
            ->orderByRaw('avg(measurements.pm25)')
            ->selectRaw('cities.name, avg(measurements.pm25) as pm25, count(distinct measurements.sensor_id) as sensors, count(distinct measurements.hour) as hours')
            ->get();

        $queryMs = round((hrtime(true) - $startedAt) / 1_000_000, 1);

        return response()->json([
            'hours' => $hours,
            'query_ms' => $queryMs,
            'cities' => $cities->map(fn (object $city): array => [
                'name' => $city->name,
                'pm25' => round((float) $city->pm25, 1),
                'sensors' => (int) $city->sensors,
                'coverage' => (int) round($city->hours / $hours * 100),
            ]),
        ]);
    }
}
