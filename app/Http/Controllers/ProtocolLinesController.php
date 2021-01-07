<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\ProtocolLine;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;

class ProtocolLinesController extends BaseController
{
    public function editPerson(int $protocolLineId): View
    {
        $protocolLine = ProtocolLine::find($protocolLineId);
        $persons = Person::all();
        return view('protocol-line.edit-person', [
            'protocolLine' => $protocolLine,
            'persons' => $persons,
        ]);
    }

    public function setPerson(int $protocolLineId, int $personId): RedirectResponse
    {
        $protocolLine = ProtocolLine::find($protocolLineId);
        $protocolLine->person_id = $personId;
        $protocolLine->save();
        return redirect("/competitions/events/{$protocolLine->event_id}/show");
    }
}
