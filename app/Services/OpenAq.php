<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenAq
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

    /**
     * @param  array<string, mixed>  $query
     * @return list<array<string, mixed>>
     */
    private function get(string $path, array $query): array
    {
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
