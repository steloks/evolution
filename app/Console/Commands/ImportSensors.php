<?php

namespace App\Console\Commands;

use App\Services\SensorImporter;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:import-sensors')]
#[Description('Sync the Bulgarian PM2.5 sensors from OpenAQ and assign them to cities')]
class ImportSensors extends Command
{
    public function handle(SensorImporter $sensorImporter): int
    {
        $this->info('Synced '.$sensorImporter->run().' sensors.');

        return self::SUCCESS;
    }
}
