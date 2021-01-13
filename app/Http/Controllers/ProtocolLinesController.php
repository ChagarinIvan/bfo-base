<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Person;
use App\Models\ProtocolLine;
use App\Services\IdentService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Collection;

class ProtocolLinesController extends BaseController
{
    public function editPerson(int $protocolLineId): View
    {
        $protocolLine = ProtocolLine::find($protocolLineId);
        $persons = Person::all();
        $persons = $persons->sortBy('lastname');

        return view('protocol-line.edit-person', [
            'protocolLine' => $protocolLine,
            'persons' => $persons,
        ]);
    }

    public function setPerson(int $protocolLineId, int $personId, Request $request): RedirectResponse
    {
        $url = $request->get('url');
        $person = Person::find($personId);
        $protocolLine = ProtocolLine::find($protocolLineId);
        $identService = new IdentService();
        $identPersonId = $identService->identPerson($protocolLine);
        if ($identPersonId !== $personId) {
            $person->setPrompt($protocolLine->getIndentLine());
            $person->save();
        }
        $protocolLine->person_id = $personId;
        $protocolLine->save();
        if ($url === null) {
            return redirect('/');
        }
        return redirect($url);
    }

    public function showNotIdent(): View
    {
        /** @var Collection|ProtocolLine[] $lines */
        $personsGroupIds = Group::where('name', 'NOT LIKE', "%10")
            ->where('name', 'NOT LIKE', "%12")
            ->where('name', 'NOT LIKE', "%14")
            ->where('name', 'NOT LIKE', "%16")
            ->get('id');

        $lines = ProtocolLine::wherePersonId(null)
            ->whereIn('group_id', $personsGroupIds)
            ->with(['event.competition', 'group'])
            ->get();

        $lines = $lines->groupBy(fn(ProtocolLine $line) => $line->lastname.'_'.$line->firstname);
        $persons = $lines->sortKeys();

        return view('protocol-line.show-not-ident', ['persons' => $persons]);
    }
}
