<?php

namespace App\Http\Controllers;

use App\Models\Competition;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class CompetitionController extends BaseController
{
    public function index()
    {
        $competitions = Competition::all();
        return view('competitions.index', ['competitions' => $competitions]);
    }

    public function create()
    {
        return view('competitions.create');
    }

    public function show(int $competitionId)
    {
        $competition = Competition::find($competitionId);
        return view('competitions.show', ['competition' => $competition]);
    }

    public function store(Request $request)
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
