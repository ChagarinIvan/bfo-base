<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Facades\System;
use App\Models\Club;
use App\Models\Person;
use App\Models\ProtocolLine;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

class PersonController extends BaseController
{
    public function index(Request $request): View
    {
        $search = (string)$request->get('search');

        $personsQuery = Person::join('protocol_lines', 'protocol_lines.person_id', '=', 'person.id')
            ->addSelect(DB::raw('ANY_VALUE(person.id) AS id'))
            ->groupBy('protocol_lines.person_id')
            ->orderByRaw(DB::raw('COUNT(protocol_lines.person_id) DESC'));

        if (strlen($search) > 0) {
            $personsQuery->where('person.firstname', 'LIKE', '%'.$search.'%')
                ->orWhere('person.lastname', 'LIKE', '%'.$search.'%');
        }
        $paginator = $personsQuery->paginate(13);
        $persons = Person::with(['protocolLines', 'club'])->find(collect($paginator->items())->pluck('id'));

        return view('persons.index', [
            'paginator' => $paginator,
            'persons' => $persons,
            'search' => $search,
        ]);
    }

    public function show(int $personId): View
    {
        /** @var Person $person */
        $person = Person::with(['protocolLines.distance.event.competition', 'protocolLines.distance.group'])->find($personId);
        /** fn features from php 7.4 */
        $groupedProtocolLines = $person->protocolLines->groupBy(fn (ProtocolLine $line) => $line->distance->event->date->format('Y'));
        $groupedProtocolLines->transform(function (Collection $protocolLines) {
            /** fn features from php 7.4 */
            return $protocolLines->sortByDesc(fn(ProtocolLine $line) => $line->distance->event->date);
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
        $person->save();
        $person->makePrompts();
        System::setNeedRecheck();

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
        $person->makePrompts();
        System::setNeedRecheck();

        return redirect("/persons");
    }

    public function delete(int $personId): RedirectResponse
    {
        $person = Person::find($personId);
        $protocolLines = ProtocolLine::wherePersonId($personId)->get();
        $protocolLines->each(function (ProtocolLine $line) {
            $line->person_id = null;
            $line->save();
        });
        $person->delete();
        return redirect("/persons");
    }
}
