<?php

namespace App\Console\Commands;

use App\Models\Sensor;
use App\Services\MeasurementImporter;
use App\Services\SensorImporter;
use Carbon\CarbonImmutable;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Throwable;

#[Signature('app:import-measurements')]
#[Description('Sync the sensors and import every hour each sensor is missing from OpenAQ')]
class ImportMeasurements extends Command
{
    public function handle(SensorImporter $sensorImporter, MeasurementImporter $measurementImporter): int
    {
        $failed = 0;

        try {
            $this->info('Synced '.$sensorImporter->run().' sensors.');
        } catch (Throwable $exception) {
            $failed++;
            report($exception);
            $this->warn('Sensor sync failed, using the sensors already stored.');
        }

        $to = CarbonImmutable::now('UTC')->startOfHour();
        $stored = 0;

        foreach (Sensor::all() as $sensor) {
            try {
                $stored += $measurementImporter->run($sensor, $to);
            } catch (Throwable $exception) {
                $failed++;
                report($exception);
            }
        }

        $this->info("Stored {$stored} measurements, {$failed} failures.");

        return $failed === 0 ? self::SUCCESS : self::FAILURE;
    }
}
