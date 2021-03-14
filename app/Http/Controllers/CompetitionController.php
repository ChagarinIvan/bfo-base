<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Competition;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;

class CompetitionController extends BaseController
{
    public function index(): View
    {
        $allCompetitions = Competition::all();
        $dates = $allCompetitions->pluck('from');
        $years = $dates->transform(fn(Carbon $date) => $date->format('Y'))->unique()->sortDesc();
        $groupedCompetitions = $allCompetitions->groupBy(function (Competition $competition) {
            return $competition->from->format('Y');
        });
        $groupedCompetitions = $groupedCompetitions->sortKeysDesc();
        $groupedCompetitions = $groupedCompetitions->transform(function(Collection $competitions) {
            return $competitions->sortByDesc(function (Competition $competition) {
                return $competition->from->format('Y-m-d');
            });
        });

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
