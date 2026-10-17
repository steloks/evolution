<?php

use App\Http\Controllers\RankingController;
use App\Models\Measurement;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $first = Measurement::min('hour');
    $last = Measurement::max('hour');

    return view('app', [
        'firstDay' => $first ? CarbonImmutable::parse($first, 'UTC')->setTimezone(RankingController::TIMEZONE)->format('Y/m/d') : null,
        'lastDay' => $last ? CarbonImmutable::parse($last, 'UTC')->setTimezone(RankingController::TIMEZONE)->format('Y/m/d') : null,
    ]);
});
