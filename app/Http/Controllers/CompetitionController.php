<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Competition;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Contracts\View\View;

class CompetitionController extends BaseController
{
    public function index(): View
    {
        $competitions = Competition::all();
        $dates = $competitions->pluck('from');
        $years = $dates->transform(fn(Carbon $date) => $date->format('Y'))->unique()->sortDesc();
        $groupedCompetitions = $competitions->groupBy(function (Competition $competition) {
            return $competition->from->format('Y');
        });
        $groupedCompetitions = $groupedCompetitions->sortKeysDesc();

        return view('competitions.index', [
            'groupedCompetitions' => $groupedCompetitions,
            'years' => $years,
        ]);
    }

    public function create(): View
    {
        return view('competitions.create');
    }

    public function show(int $competitionId): View
    {
        $competition = Competition::find($competitionId);
        return view('competitions.show', ['competition' => $competition]);
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
}
