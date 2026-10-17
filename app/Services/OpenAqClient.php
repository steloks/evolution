<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;
use RuntimeException;

class OpenAqClient
{
    public const int PM25_PARAMETER_ID = 2;

    /** @return list<array<string, mixed>> */
    public function pm25Locations(string $countryCode): array
    {
        return $this->get('/locations', [
            'iso' => $countryCode,
            'parameters_id' => self::PM25_PARAMETER_ID,
            'limit' => 1000,
        ]);
    }

    /** @return list<array<string, mixed>> */
    public function sensorHours(int $sensorId, CarbonImmutable $from, CarbonImmutable $to): array
    {
        return $this->get("/sensors/{$sensorId}/hours", [
            'datetime_from' => $from->toIso8601ZuluString(),
            'datetime_to' => $to->toIso8601ZuluString(),
            'limit' => 1000,
        ]);
    }

    /**
     * @param  array<string, mixed>  $query
     * @return list<array<string, mixed>>
     */
    private function get(string $path, array $query): array
    {
        Sleep::for(1)->second();

        $results = $this->client()->get($path, $query)->throw()->json('results');

        if (! is_array($results) || ! array_is_list($results)) {
            throw new RuntimeException("OpenAQ returned an unexpected payload for {$path}.");
        }

        return $results;
    }

    private function client(): PendingRequest
    {
        return Http::baseUrl(config('services.openaq.url'))
            ->withHeaders(['X-API-Key' => (string) config('services.openaq.key')])
            ->acceptJson()
            ->timeout(config('services.openaq.timeout'))
            ->retry(3, 5_000);
    }
}
