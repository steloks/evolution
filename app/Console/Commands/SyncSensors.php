<?php

namespace App\Console\Commands;

use App\Services\SensorSync;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:sync-sensors')]
#[Description('Fetch the PM2.5 sensors in Bulgaria from OpenAQ and assign them to cities')]
class SyncSensors extends Command
{
    public function handle(SensorSync $sensorSync): int
    {
        $count = $sensorSync->run();

        $this->info("Synced {$count} sensors.");

        return self::SUCCESS;
    }
}
