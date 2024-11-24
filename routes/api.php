<?php

use App\Http\Controllers\Api\ChirpStackWebhookController;
use App\Http\Controllers\Api\ThingsBoardWebhookController;
use App\Http\Controllers\Monitoring\Api\ChirpStackController;
use App\Http\Controllers\Monitoring\Api\ThingsBoardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// ChirpStack webhook endpoints
Route::post('/webhooks/chirpstack/uplink', [ChirpStackWebhookController::class, 'handleUplink']);

// ThingsBoard webhook endpoints
Route::post('/webhooks/thingsboard/rpc', [ThingsBoardWebhookController::class, 'handleRpc']);

Route::prefix('monitoring')->group(function () {
    Route::post('/chirpstack', [ChirpStackController::class, 'handleWebhook']);
    Route::post('/thingsboard', [ThingsBoardController::class, 'handleWebhook']);
});
