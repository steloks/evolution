<?php

namespace App\Console\Commands;

use App\Models\Sensor;
use App\Services\MeasurementImporter;
use Carbon\CarbonImmutable;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Throwable;

#[Signature('app:import-measurements')]
#[Description('Import every hour each sensor is missing from OpenAQ')]
class ImportMeasurements extends Command
{
    public function handle(MeasurementImporter $measurementImporter): int
    {
        $to = CarbonImmutable::now('UTC')->startOfHour();
        $stored = 0;
        $failed = 0;

        foreach (Sensor::all() as $sensor) {
            try {
                $stored += $measurementImporter->run($sensor, $to);
            } catch (Throwable $exception) {
                $failed++;
                report($exception);
            }
        }

        $this->info("Stored {$stored} measurements, {$failed} sensors failed.");

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
