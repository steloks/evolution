<?php

use App\Http\Controllers\RankingController;
use Illuminate\Support\Facades\Route;

Route::get('/rankings', RankingController::class);
