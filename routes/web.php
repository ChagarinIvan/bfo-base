<?php

use App\Http\Controllers\CompetitionController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\PersonController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
//competitions
Route::redirect('/', '/competitions');
Route::get('/competitions', [CompetitionController::class, 'index']);
Route::get('/competitions/create', [CompetitionController::class, 'create']);
Route::post('/competitions/store', [CompetitionController::class, 'store']);
Route::get('/competitions/{competition}/show', [CompetitionController::class, 'show']);
//event
Route::get('/competitions/{competition}/events/add', [EventController::class, 'create']);
Route::get('/competitions/events/{event}/edit', [EventController::class, 'edit']);
Route::get('/competitions/events/{eventId}/show', [EventController::class, 'show']);
Route::post('/competitions/{competition}/events/store', [EventController::class, 'store']);
Route::patch('/competitions/events/{event}/update', [EventController::class, 'update']);
Route::get('/competitions/events/{event}/delete', [EventController::class, 'delete']);
//persons
Route::get('/persons', [PersonController::class, 'index']);
