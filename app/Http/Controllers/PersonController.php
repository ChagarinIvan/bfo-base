<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\ProtocolLine;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Contracts\View\View;

class PersonController extends BaseController
{
    public function index(): View
    {
        $persons = Person::with(['protocolLines', 'club'])->orderBy('lastname')->paginate(13);

        return view('persons.index', ['persons' => $persons,]);
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
        return view('persons.create');
    }

    public function store(int $personId): View
    {
        /** @var Person $person */
        $person = Person::with(['protocolLines.event.competition', 'protocolLines.group'])->find($personId);
        $groupedProtocolLines = $person->protocolLines->groupBy(function (ProtocolLine $line) {
            return $line->event->date->format('Y');
        });
        $groupedProtocolLines = $groupedProtocolLines->sortKeysDesc();
        return view('persons.show', ['person' => $person, 'groupedProtocolLines' => $groupedProtocolLines]);
    }
}
