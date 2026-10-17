<?php

namespace App\Console\Commands;

use App\Models\Sensor;
use App\Services\MeasurementImporter;
use App\Services\Metrics;
use Carbon\CarbonImmutable;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

#[Signature('app:import-measurements')]
#[Description('Import every hour each sensor is missing from OpenAQ')]
class ImportMeasurements extends Command
{
    public function handle(MeasurementImporter $measurementImporter, Metrics $metrics): int
    {
        $startedAt = microtime(true);
        $to = CarbonImmutable::now('UTC')->startOfHour();
        $stored = 0;
        $failed = 0;

        foreach (Sensor::all() as $sensor) {
            try {
                $stored += $measurementImporter->run($sensor, $to);
            } catch (Throwable $exception) {
                $failed++;
                Log::warning('Sensor import failed', ['sensor_id' => $sensor->id, 'error' => $exception->getMessage()]);
            }
        }

        $duration = microtime(true) - $startedAt;
        $metrics->recordImport($stored, $failed, $duration);
        Log::info('Measurements imported', ['stored' => $stored, 'failed_sensors' => $failed, 'duration_seconds' => round($duration, 1)]);

        $this->info("Stored {$stored} measurements, {$failed} sensors failed.");

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
