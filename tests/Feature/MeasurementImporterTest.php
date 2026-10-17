<?php

namespace Tests\Feature;

use App\Models\Measurement;
use App\Models\Sensor;
use App\Services\MeasurementImporter;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Sleep;
use Tests\TestCase;

class MeasurementImporterTest extends TestCase
{
    use RefreshDatabase;

    private CarbonImmutable $until;

    protected function setUp(): void
    {
        parent::setUp();

        Sleep::fake();
        $this->until = CarbonImmutable::parse('2026-10-17 12:00:00', 'UTC');
    }

    public function test_a_new_sensor_imports_six_months_in_thirty_day_chunks(): void
    {
        Http::fake(['*' => Http::response(['results' => []])]);
        $sensor = Sensor::factory()->create();

        app(MeasurementImporter::class)->run($sensor, $this->until);

        Http::assertSentCount(7);
        Http::assertSent(fn (Request $request): bool => $request['datetime_from'] === '2026-04-17T12:00:00Z'
            && $request['datetime_to'] === '2026-05-17T12:00:00Z');
        $this->assertEquals($this->until, $sensor->fresh()->imported_until);
    }

    public function test_it_continues_from_where_it_stopped_with_a_few_hours_back_for_late_data(): void
    {
        Http::fake(['*' => Http::response(['results' => []])]);
        $sensor = Sensor::factory()->create(['imported_until' => $this->until->subHour()]);

        app(MeasurementImporter::class)->run($sensor, $this->until);

        Http::assertSentCount(1);
        Http::assertSent(fn (Request $request): bool => $request['datetime_from'] === '2026-10-17T08:00:00Z');
    }

    public function test_it_never_goes_further_back_than_six_months(): void
    {
        Http::fake(['*' => Http::response(['results' => []])]);
        $sensor = Sensor::factory()->create(['imported_until' => $this->until->subYear()]);

        app(MeasurementImporter::class)->run($sensor, $this->until);

        Http::assertSent(fn (Request $request): bool => $request['datetime_from'] === '2026-04-17T12:00:00Z');
        Http::assertNotSent(fn (Request $request): bool => $request['datetime_from'] < '2026-04-17');
    }

    public function test_it_stores_valid_hours_and_skips_bad_ones(): void
    {
        Http::fake(['*' => Http::response(['results' => [
            $this->hour('2026-10-17T09:00:00Z', 12.345),
            $this->hour('2026-10-17T10:00:00Z', null),
            $this->hour('2026-10-17T11:00:00Z', -3),
            ['value' => 7],
        ]])]);
        $sensor = Sensor::factory()->create(['imported_until' => $this->until->subHour()]);

        $stored = app(MeasurementImporter::class)->run($sensor, $this->until);

        $this->assertSame(1, $stored);
        $this->assertSame(1, Measurement::count());
        $this->assertSame(12.35, Measurement::first()->pm25);
        $this->assertSame('2026-10-17 09:00:00', Measurement::first()->hour->toDateTimeString());
    }

    public function test_importing_the_same_hour_again_updates_it_instead_of_duplicating(): void
    {
        Http::fake(['*' => Http::sequence()
            ->push(['results' => [$this->hour('2026-10-17T10:00:00Z', 5)]])
            ->push(['results' => [$this->hour('2026-10-17T10:00:00Z', 8)]])]);
        $sensor = Sensor::factory()->create(['imported_until' => $this->until->subHour()]);

        app(MeasurementImporter::class)->run($sensor, $this->until);
        app(MeasurementImporter::class)->run($sensor->fresh(), $this->until);

        $this->assertSame(1, Measurement::count());
        $this->assertSame(8.0, Measurement::first()->pm25);
    }

    public function test_a_failing_source_keeps_the_stored_data_and_the_position(): void
    {
        Http::fake(['*' => Http::response('down', 500)]);
        $sensor = Sensor::factory()->create(['imported_until' => $this->until->subHour()]);
        Measurement::factory()->for($sensor)->create();

        try {
            app(MeasurementImporter::class)->run($sensor, $this->until);
            $this->fail('The import should have failed.');
        } catch (\Throwable) {
        }

        $this->assertSame(1, Measurement::count());
        $this->assertEquals($this->until->subHour(), $sensor->fresh()->imported_until);
    }

    public function test_an_unexpected_payload_is_rejected(): void
    {
        Http::fake(['*' => Http::response(['error' => 'oops'])]);
        $sensor = Sensor::factory()->create(['imported_until' => $this->until->subHour()]);

        $this->expectExceptionMessage('unexpected payload');

        app(MeasurementImporter::class)->run($sensor, $this->until);
    }

    /** @return array<string, mixed> */
    private function hour(string $startsAt, mixed $value): array
    {
        return ['value' => $value, 'period' => ['datetimeFrom' => ['utc' => $startsAt]]];
    }
}
