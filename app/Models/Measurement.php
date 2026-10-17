<?php

namespace App\Models;

use Database\Factories\MeasurementFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['sensor_id', 'hour', 'pm25'])]
class Measurement extends Model
{
    /** @use HasFactory<MeasurementFactory> */
    use HasFactory;

    public $timestamps = false;

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'hour' => 'immutable_datetime',
            'pm25' => 'float',
        ];
    }

    /** @return BelongsTo<Sensor, $this> */
    public function sensor(): BelongsTo
    {
        return $this->belongsTo(Sensor::class);
    }
}
