<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Competition;
use App\Models\Distance;
use App\Models\Event;
use App\Models\ProtocolLine;
use App\Models\Year;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;

class CompetitionController extends BaseController
{
    public function index(int $year): View|RedirectResponse
    {
        if ($year === 0) {
            $year = Carbon::now()->year;
            return redirect("/competitions/y{$year}");
        }

        $yearCompetitions = Competition::where('from', '>=', "{$year}-01-01")
            ->where('to', '<=', "{$year}-12-31")
            ->orderByDesc('from')
            ->get();

        return view('competitions.index', [
            'competitions' => $yearCompetitions,
            'years' => Year::YEARS,
            'selectedYear' => $year,
        ]);
    }

    public function create(int $year): View
    {
        return view('competitions.create', ['year' => $year,]);
    }

    public function show(int $competitionId): View
    {
        $competition = Competition::find($competitionId);
        $events = Event::whereCompetitionId($competitionId)
            ->orderBy('date')
            ->get();

        return view('competitions.show', [
            'competition' => $competition,
            'events' => $events,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $formParams = $request->validate([
            'name' => 'required|unique:competitions|max:255',
            'description' => 'nullable',
            'from' => 'required|date',
            'to' => 'required|date',
        ]);

        $competition = new Competition($formParams);
        $competition->save();
        return redirect("/competitions/{$competition->id}/show");
    }

    public function delete(int $year, int $competitionId): RedirectResponse
    {
        Competition::destroy($competitionId);
        $events = Event::whereCompetitionId($competitionId)->get();
        $eventsIds = $events->pluck('id');
        Event::destroy($eventsIds);
        $distances = Distance::whereIn('event_id', $eventsIds)->get();
        $distancesIds = $distances->pluck('id');
        Distance::destroy($distancesIds);
        ProtocolLine::whereIn('distance_id', $distancesIds)->get();
        return redirect("/competitions/y{$year}");
    }
}
