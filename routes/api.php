<?php

use App\Http\Controllers\HealthController;
use App\Http\Controllers\MetricsController;
use App\Http\Controllers\RankingController;
use Illuminate\Support\Facades\Route;

Route::get('/rankings', RankingController::class);
Route::get('/health', HealthController::class);
Route::get('/metrics', MetricsController::class);
