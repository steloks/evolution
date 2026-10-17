<?php

namespace App\Console\Commands;

use App\Services\SensorImporter;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

#[Signature('app:import-sensors')]
#[Description('Sync the Bulgarian PM2.5 sensors from OpenAQ and assign them to cities')]
class ImportSensors extends Command
{
    public function handle(SensorImporter $sensorImporter): int
    {
        $synced = $sensorImporter->run();

        Log::info('Sensors synced', ['sensors' => $synced]);
        $this->info("Synced {$synced} sensors.");

        return self::SUCCESS;
    }
}
