<?php

use App\Http\Controllers;
use App\Models\Year;
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

//default route
Route::any('/', fn() => redirect()->action(Controllers\Competition\ShowCompetitionsTableAction::class, ['year' => Year::actualYear()]));

//competitions
Route::prefix('competitions')->group(function () {
    Route::get('y{year}',            Controllers\Competition\ShowCompetitionsTableAction::class);
    Route::get('{competition}/show', Controllers\Competition\ShowCompetitionAction::class);

    Route::middleware(['auth'])->group(function () {
        Route::get( 'y{year}/create',               Controllers\Competition\ShowCreateFormAction::class);
        Route::get( 'y{year}/delete/{competition}', Controllers\Competition\DeleteCompetitionAction::class);
        Route::post('store',                        Controllers\Competition\StoreCompetitionAction::class);
    });
});
//event
Route::get('/competitions/events/{event}/show', [Controllers\EventController::class, 'show']);
Route::middleware(['auth'])->group(function () {
    Route::get('/competitions/{competition}/events/add', [Controllers\EventController::class, 'create']);
    Route::get('/competitions/{competition}/events/sum', [Controllers\EventController::class, 'sum']);
    Route::post('/competitions/{competition}/events/unit', [Controllers\EventController::class, 'unit']);
    Route::get('/competitions/events/{event}/edit', [Controllers\EventController::class, 'edit']);
    Route::post('/competitions/{competition}/events/store', [Controllers\EventController::class, 'store']);
    Route::post('/competitions/events/{event}/update', [Controllers\EventController::class, 'update']);
    Route::get('/competitions/events/{event}/delete', [Controllers\EventController::class, 'delete']);
    Route::get('/competitions/events/{event}/add-flags', [Controllers\EventController::class, 'addFlags']);
    Route::get('/competitions/events/{event}/set-flag/{flag}', [Controllers\EventController::class, 'setFlags']);
    Route::get('/competitions/events/{event}/delete-flag/{flag}', [Controllers\EventController::class, 'deleteFlags']);
});

//persons
Route::get('/persons', [Controllers\PersonController::class, 'index']);
Route::get('/persons/{person}/show', [Controllers\PersonController::class, 'show']);
Route::middleware(['auth'])->group(function () {
    Route::get('/persons/create', [Controllers\PersonController::class, 'create']);
    Route::post('/persons/store', [Controllers\PersonController::class, 'store']);
    Route::get('/persons/{person}/delete', [Controllers\PersonController::class, 'delete']);
    Route::get('/persons/{person}/edit', [Controllers\PersonController::class, 'edit']);
    Route::post('/persons/{person}/update', [Controllers\PersonController::class, 'update']);
});
//clubs
Route::get('club', [Controllers\ClubController::class, 'index']);
Route::get('/club/{club}/show', [Controllers\ClubController::class, 'show']);
//protocol-line
Route::middleware(['auth'])->group(function () {
    Route::get('/protocol-lines/{protocolLine}/edit-person', [Controllers\ProtocolLinesController::class, 'editPerson']);
    Route::get('/protocol-lines/{protocolLine}/set-person/{person}', [Controllers\ProtocolLinesController::class, 'setPerson']);
});
//localization
Route::get('/localization/{code}', [Controllers\LocalizationController::class, 'changeLocale']);
//flags
Route::get('/flags', [Controllers\FlagsController::class, 'index']);
Route::get('/flags/{flag}/show-events', [Controllers\FlagsController::class, 'showEvents']);
Route::middleware(['auth'])->group(function () {
    Route::get('/flags/create', [Controllers\FlagsController::class, 'create']);
    Route::get('/flags/{flag}/edit', [Controllers\FlagsController::class, 'edit']);
    Route::post('/flags/store', [Controllers\FlagsController::class, 'store']);
    Route::post('/flags/{flag}/update', [Controllers\FlagsController::class, 'update']);
    Route::get('/flags/{flag}/delete', [Controllers\FlagsController::class, 'delete']);
});
//faq
Route::get('/faq', [Controllers\FaqController::class, 'index']);
Route::get('/faq-api', [Controllers\FaqController::class, 'api']);

Route::get('/404', Controllers\Error\Show404ErrorAction::class);
//cups
Route::get('/cups/y{year}', [Controllers\CupController::class, 'index']);
Route::get('/cups/{cup}/show', [Controllers\CupController::class, 'show']);
Route::get('/cups/{cup}/table/{group}', [Controllers\CupController::class, 'table']);
Route::middleware(['auth'])->group(function () {
    Route::get('/cups/y{year}/create', [Controllers\CupController::class, 'create']);
    Route::get('/cups/{cup}/edit', [Controllers\CupController::class, 'edit']);
    Route::get('/cups/{cup}/delete', [Controllers\CupController::class, 'delete']);
    Route::post('/cups/store', [Controllers\CupController::class, 'store']);
    Route::post('/cups/{cup}/update', [Controllers\CupController::class, 'update']);
});
//cup-events
Route::get('/cups/{cup}/events/{cupEvent}/show/{group}', [Controllers\CupEventController::class, 'show']);
Route::middleware(['auth'])->group(function () {
    Route::get('/cups/{cup}/events/create', [Controllers\CupEventController::class, 'create']);
    Route::get('/cups/{cup}/events/{cupEvent}/delete', [Controllers\CupEventController::class, 'delete']);
    Route::get('/cups/{cup}/events/{cupEvent}/edit', [Controllers\CupEventController::class, 'edit']);
    Route::post('/cups/{cup}/events/{cupEvent}/update', [Controllers\CupEventController::class, 'update']);
    Route::post('/cups/{cup}/events/store', [Controllers\CupEventController::class, 'store']);
});

//auth group
Route::get('/login', Controllers\Login\ShowLoginFormAction::class);
Route::post('/sign-in', Controllers\Login\SignInAction::class);

Route::middleware(['auth'])->group(function () {
    Route::get( '/registration',      Controllers\Registration\ShowRegistrationFormAction::class);
    Route::post('/registration/data', Controllers\Registration\SendRegistrationDataAction::class);
    Route::post('/registration/data', Controllers\Registration\SendRegistrationDataAction::class);
});

Route::get('/login/auth/{token}', Controllers\Login\TokenAuthAction::class);

