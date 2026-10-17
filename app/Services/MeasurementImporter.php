<?php

namespace App\Services;

use App\Models\Measurement;
use App\Models\Sensor;
use Carbon\CarbonImmutable;

class MeasurementImporter
{
    public const int HISTORY_DAYS = 183;

    private const int DAYS_PER_REQUEST = 30;

    private const int LATE_DATA_HOURS = 3;

    public function __construct(private OpenAqClient $openAq) {}

    public function run(Sensor $sensor, CarbonImmutable $until): int
    {
        $stored = 0;
        $chunkStart = $this->importStart($sensor, $until);

        while ($chunkStart < $until) {
            $chunkEnd = $chunkStart->addDays(self::DAYS_PER_REQUEST)->min($until);

            $stored += $this->importChunk($sensor, $chunkStart, $chunkEnd);
            $sensor->update(['imported_until' => $chunkEnd]);

            $chunkStart = $chunkEnd;
        }

        return $stored;
    }

    private function importStart(Sensor $sensor, CarbonImmutable $until): CarbonImmutable
    {
        $oldestAllowed = $until->subDays(self::HISTORY_DAYS);

        if ($sensor->imported_until === null) {
            return $oldestAllowed;
        }

        return $sensor->imported_until->subHours(self::LATE_DATA_HOURS)->max($oldestAllowed);
    }

    private function importChunk(Sensor $sensor, CarbonImmutable $from, CarbonImmutable $to): int
    {
        $rows = [];

        foreach ($this->openAq->sensorHours($sensor->id, $from, $to) as $hour) {
            $row = $this->toRow($sensor, $hour);

            if ($row !== null) {
                $rows[] = $row;
            }
        }

        Measurement::upsert($rows, ['sensor_id', 'hour'], ['pm25']);

        return count($rows);
    }

    /**
     * @param  array<string, mixed>  $hour
     * @return array{sensor_id: int, hour: string, pm25: float}|null
     */
    private function toRow(Sensor $sensor, array $hour): ?array
    {
        $value = data_get($hour, 'value');
        $startsAt = data_get($hour, 'period.datetimeFrom.utc');

        if (! is_numeric($value) || $value < 0 || ! is_string($startsAt)) {
            return null;
        }

        return [
            'sensor_id' => $sensor->id,
            'hour' => CarbonImmutable::parse($startsAt)->utc()->toDateTimeString(),
            'pm25' => round((float) $value, 2),
        ];
    }
}
