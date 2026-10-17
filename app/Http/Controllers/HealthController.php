<?php

namespace App\Http\Controllers;

use App\Models\Measurement;
use App\Services\Metrics;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Throwable;

class HealthController extends Controller
{
    public const int STALE_AFTER_HOURS = 6;

    public function __invoke(Metrics $metrics): JsonResponse
    {
        try {
            $latest = Measurement::max('hour');
        } catch (Throwable $exception) {
            report($exception);

            return response()->json(['status' => 'down', 'database' => 'down'], 503);
        }

        $latestHour = $latest ? CarbonImmutable::parse($latest, 'UTC') : null;
        $isFresh = $latestHour !== null && $latestHour->diffInHours(now()) <= self::STALE_AFTER_HOURS;

        return response()->json([
            'status' => $isFresh ? 'ok' : 'stale',
            'database' => 'up',
            'latest_measurement' => $latestHour?->toIso8601ZuluString(),
            'last_import' => $metrics->lastImport(),
        ]);
    }
}
