<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiController;

/*
|--------------------------------------------------------------------------
| API Routes — Petani Maju Mobile App API
|--------------------------------------------------------------------------
*/

Route::get('/tips', [ApiController::class, 'getTips']);
Route::get('/tips/{id}', [ApiController::class, 'getTipById']);

Route::get('/hama', [ApiController::class, 'getHama']);
Route::get('/hama/{id}', [ApiController::class, 'getHamaById']);
