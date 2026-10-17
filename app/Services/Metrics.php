<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class Metrics
{
    private const string LAST_IMPORT = 'metrics.last_import';

    private const string RANKING_REQUESTS = 'metrics.ranking_requests';

    private const string RANKING_QUERY_MICROSECONDS = 'metrics.ranking_query_microseconds';

    public function recordImport(int $stored, int $failedSensors, float $durationSeconds): void
    {
        Cache::forever(self::LAST_IMPORT, [
            'finished_at' => now()->getTimestamp(),
            'duration_seconds' => round($durationSeconds, 1),
            'stored' => $stored,
            'failed_sensors' => $failedSensors,
        ]);
    }

    /** @return array{finished_at: int, duration_seconds: float, stored: int, failed_sensors: int}|null */
    public function lastImport(): ?array
    {
        return Cache::get(self::LAST_IMPORT);
    }

    public function recordRanking(float $queryMs): void
    {
        Cache::add(self::RANKING_REQUESTS, 0);
        Cache::add(self::RANKING_QUERY_MICROSECONDS, 0);
        Cache::increment(self::RANKING_REQUESTS);
        Cache::increment(self::RANKING_QUERY_MICROSECONDS, (int) round($queryMs * 1000));
    }

    /** @return array{requests: int, query_seconds: float} */
    public function rankings(): array
    {
        return [
            'requests' => (int) Cache::get(self::RANKING_REQUESTS, 0),
            'query_seconds' => (int) Cache::get(self::RANKING_QUERY_MICROSECONDS, 0) / 1_000_000,
        ];
    }
}
