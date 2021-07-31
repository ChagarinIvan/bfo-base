<?php

use App\Http\Controllers\ApiCallbackController;
use App\Http\Controllers\ClubController;
use App\Http\Controllers\CompetitionController;
use App\Http\Controllers\CupController;
use App\Http\Controllers\CupEventController;
use App\Http\Controllers\ErrorController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\FlagsController;
use App\Http\Controllers\LocalizationController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\ProtocolLinesController;
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
Route::redirect('/', '/competitions/y0');
Route::get('/competitions/y{year}', [CompetitionController::class, 'index']);
Route::get('/competitions/y{year}/create', [CompetitionController::class, 'create']);
Route::post('/competitions/store', [CompetitionController::class, 'store']);
Route::get('/competitions/{competition}/show', [CompetitionController::class, 'show']);
Route::get('/competitions/y{year}/delete/{competition}', [CompetitionController::class, 'delete']);
//event
Route::get('/competitions/{competition}/events/add', [EventController::class, 'create']);
Route::get('/competitions/{competition}/events/sum', [EventController::class, 'sum']);
Route::post('/competitions/{competition}/events/unit', [EventController::class, 'unit']);
Route::get('/competitions/events/{event}/edit', [EventController::class, 'edit']);
Route::get('/competitions/events/{event}/show', [EventController::class, 'show']);
Route::post('/competitions/{competition}/events/store', [EventController::class, 'store']);
Route::post('/competitions/events/{event}/update', [EventController::class, 'update']);
Route::get('/competitions/events/{event}/delete', [EventController::class, 'delete']);
Route::get('/competitions/events/{event}/add-flags', [EventController::class, 'addFlags']);
Route::get('/competitions/events/{event}/set-flag/{flag}', [EventController::class, 'setFlags']);
Route::get('/competitions/events/{event}/delete-flag/{flag}', [EventController::class, 'deleteFlags']);
//persons
Route::get('/persons', [PersonController::class, 'index']);
Route::get('/persons/{person}/show', [PersonController::class, 'show']);
Route::get('/persons/create', [PersonController::class, 'create']);
Route::post('/persons/store', [PersonController::class, 'store']);
Route::get('/persons/{person}/delete', [PersonController::class, 'delete']);
Route::get('/persons/{person}/edit', [PersonController::class, 'edit']);
Route::post('/persons/{person}/update', [PersonController::class, 'update']);
//clubs
Route::get('club', [ClubController::class, 'index']);
Route::get('/club/{club}/show', [ClubController::class, 'show']);
//protocol-line
Route::get('/protocol-lines/{protocolLine}/edit-person', [ProtocolLinesController::class, 'editPerson']);
Route::get('/protocol-lines/{protocolLine}/set-person/{person}', [ProtocolLinesController::class, 'setPerson']);
//localization
Route::get('/localization/{code}', [LocalizationController::class, 'changeLocale']);
//flags
Route::get('/flags', [FlagsController::class, 'index']);
Route::get('/flags/create', [FlagsController::class, 'create']);
Route::get('/flags/{flag}/edit', [FlagsController::class, 'edit']);
Route::post('/flags/store', [FlagsController::class, 'store']);
Route::post('/flags/{flag}/update', [FlagsController::class, 'update']);
Route::get('/flags/{flag}/delete', [FlagsController::class, 'delete']);
Route::get('/flags/{flag}/show-events', [FlagsController::class, 'showEvents']);
//faq
Route::get('/faq', [FaqController::class, 'index']);
Route::get('/faq-api', [FaqController::class, 'api']);

Route::get('/404', [ErrorController::class, 'action404']);
//cups
Route::get('/cups/y{year}', [CupController::class, 'index']);
Route::get('/cups/y{year}/create', [CupController::class, 'create']);
Route::get('/cups/{cup}/show', [CupController::class, 'show']);
Route::get('/cups/{cup}/edit', [CupController::class, 'edit']);
Route::get('/cups/{cup}/delete', [CupController::class, 'delete']);
Route::get('/cups/{cup}/table/{group}', [CupController::class, 'table']);
Route::post('/cups/store', [CupController::class, 'store']);
Route::post('/cups/{cup}/update', [CupController::class, 'update']);
//cup-events
Route::get('/cups/{cup}/events/create', [CupEventController::class, 'create']);
Route::get('/cups/{cup}/events/{cupEvent}/delete', [CupEventController::class, 'delete']);
Route::get('/cups/{cup}/events/{cupEvent}/edit', [CupEventController::class, 'edit']);
Route::get('/cups/{cup}/events/{cupEvent}/show/{group}', [CupEventController::class, 'show']);
Route::post('/cups/{cup}/events/{cupEvent}/update', [CupEventController::class, 'update']);
Route::post('/cups/{cup}/events/store', [CupEventController::class, 'store']);
