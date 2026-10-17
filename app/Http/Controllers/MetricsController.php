<?php

namespace App\Http\Controllers;

use App\Models\Measurement;
use App\Models\Sensor;
use App\Services\Metrics;
use Carbon\CarbonImmutable;
use Illuminate\Http\Response;

class MetricsController extends Controller
{
    public function __invoke(Metrics $metrics): Response
    {
        $latest = Measurement::max('hour');
        $lastImport = $metrics->lastImport();
        $rankings = $metrics->rankings();

        $values = [
            'clean_air_sensors' => ['gauge', 'Sensors assigned to a city', Sensor::count()],
            'clean_air_measurements' => ['gauge', 'Stored hourly measurements', Measurement::count()],
            'clean_air_last_import_timestamp_seconds' => ['gauge', 'When the last measurement import finished', $lastImport['finished_at'] ?? null],
            'clean_air_last_import_duration_seconds' => ['gauge', 'How long the last measurement import took', $lastImport['duration_seconds'] ?? null],
            'clean_air_last_import_stored' => ['gauge', 'Measurements stored by the last import', $lastImport['stored'] ?? null],
            'clean_air_last_import_failed_sensors' => ['gauge', 'Sensors that failed in the last import', $lastImport['failed_sensors'] ?? null],
            'clean_air_ranking_requests_total' => ['counter', 'Ranking requests served', $rankings['requests']],
            'clean_air_ranking_query_seconds_total' => ['counter', 'Time spent in ranking queries', $rankings['query_seconds']],
        ];

        $lines = [];

        foreach ($values as $name => [$type, $help, $value]) {
            if ($value === null) {
                continue;
            }

            $lines[] = "# HELP {$name} {$help}";
            $lines[] = "# TYPE {$name} {$type}";
            $lines[] = "{$name} {$value}";
        }

        return response(implode("\n", $lines) . "\n")->header('Content-Type', 'text/plain; version=0.0.4');
    }
}
