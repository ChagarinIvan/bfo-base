<?php

use App\Http\Controllers\Api\ClubsController;
use App\Http\Controllers\Api\CompetitionController;
use App\Http\Controllers\Api\EventsController;
use App\Http\Controllers\Api\PersonsController;
use App\Http\Controllers\Api\ResultsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::get('/api/competitions', [CompetitionController::class, 'index']);
Route::get('/api/competition/{competition_id}/events', [EventsController::class, 'index']);
Route::get('/api/event/{event_id}/results', [ResultsController::class, 'index']);
Route::get('/api/clubs', [ClubsController::class, 'index']);
Route::get('/api/persons', [PersonsController::class, 'index']);
