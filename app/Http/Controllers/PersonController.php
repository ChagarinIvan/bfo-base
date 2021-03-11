<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Club;
use App\Models\Person;
use App\Models\ProtocolLine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\URL;

class PersonController extends BaseController
{
    public function index(Request $request): View
    {
        $search = (string)$request->get('search');
        $personsQuery = Person::with(['protocolLines', 'club'])->orderBy('lastname');
        if(strlen($search) > 0) {
            $personsQuery->where('firstname', 'LIKE', '%'.$search.'%')
                ->orWhere('lastname', 'LIKE', '%'.$search.'%');
        }
        $persons = $personsQuery->paginate(13);

        return view('persons.index', ['persons' => $persons, 'search' => $search]);
    }

    public function show(int $personId): View
    {
        /** @var Person $person */
        $person = Person::with(['protocolLines.event.competition', 'protocolLines.group'])->find($personId);
        $groupedProtocolLines = $person->protocolLines->groupBy(function (ProtocolLine $line) {
            return $line->event->date->format('Y');
        });
        $groupedProtocolLines = $groupedProtocolLines->sortKeysDesc();
        return view('persons.show', ['person' => $person, 'groupedProtocolLines' => $groupedProtocolLines]);
    }

    public function create(): View
    {
        $clubs = Club::orderBy('name')->get();
        return view('persons.create', ['clubs' => $clubs]);
    }

    public function edit(int $personId): View
    {
        $person = Person::find($personId);
        $clubs = Club::orderBy('name')->get();
        return view('persons.edit', ['person' => $person, 'clubs' => $clubs, 'redirect' => Url::previous()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $redirectUrl = (string)$request->get('redirect');
        $formParams = $request->validate([
            'lastname' => 'required',
            'firstname' => 'required',
            'birthday' => 'required|date',
            'club_id' => 'required|int',
        ]);

        $person = new Person($formParams);
        if ($person->club_id === 0) {
            $person->club_id = null;
        }
        $person->prompt = '[]';
        $person->save();
        return redirect(strlen($redirectUrl) > 0 ? $redirectUrl : '/persons');
    }

    public function update(Request $request, int $personId): RedirectResponse
    {
        $formParams = $request->validate([
            'lastname' => 'required',
            'firstname' => 'required',
            'birthday' => 'required|date',
            'club_id' => 'required|int',
        ]);
        $person = Person::find($personId);
        $person->fill($formParams);
        if ($person->club_id === 0) {
            $person->club_id = null;
        }

        $person->save();
        return redirect("/persons");
    }

    public function delete(int $personId): RedirectResponse
    {
        $person = Person::find($personId);
        $person->delete();
        return redirect("/persons");
    }
}
