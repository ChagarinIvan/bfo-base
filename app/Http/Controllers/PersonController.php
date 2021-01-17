<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Person;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Contracts\View\View;

class PersonController extends BaseController
{
    public function index(): View
    {
        $persons = Person::with(['protocolLines', 'club'])->get();
        $persons = $persons->sortBy('lastname');
        return view('persons.index', [
            'title' => __('app.persons.title'),
            'persons' => $persons,
        ]);
    }

    public function show(int $personId): View
    {
        $person = Person::with(['protocolLines.event.competition', 'protocolLines.group'])->find($personId);
        return view('persons.show', ['person' => $person]);
    }
}
