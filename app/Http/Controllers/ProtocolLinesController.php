<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Facades\System;
use App\Models\Person;
use App\Models\PersonPrompt;
use App\Models\ProtocolLine;
use App\Services\IdentService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class ProtocolLinesController extends BaseController
{
    public function editPerson(Request $request, int $protocolLineId): View
    {
        if (!Session::exists('prev_url')) {
            Session::put('prev_url', url()->previous());
        }
        $search = (string)$request->get('search');
        $protocolLine = ProtocolLine::find($protocolLineId);
        $personsQuery = Person::with('club')->orderBy('lastname');
        if(strlen($search) > 0) {
            $personsQuery->where('firstname', 'LIKE', '%'.$search.'%')
                ->orWhere('lastname', 'LIKE', '%'.$search.'%')
                ->orWhere('birthday', 'LIKE', '%'.$search.'%');
        }
        $persons = $personsQuery->paginate(13);

        return view('protocol-line.edit-person', [
            'protocolLine' => $protocolLine,
            'persons' => $persons,
            'search' => $search,
        ]);
    }

    public function setPerson(int $protocolLineId, int $personId): RedirectResponse
    {
        $protocolLine = ProtocolLine::find($protocolLineId);
        $identService = new IdentService();
        $identPersonId = $identService->identPerson($protocolLine->prepared_line);

        if ($identPersonId !== $personId) {
            $prompt = new PersonPrompt();
            $prompt->person_id = $personId;
            $prompt->prompt = $protocolLine->prepared_line;
            $prompt->save();

            System::setNeedRecheck(true);
        }

        $protocolLinesToRecheck = ProtocolLine::wherePreparedLine($protocolLine->prepared_line)->get();

        foreach ($protocolLinesToRecheck as $protocolLine) {
            $protocolLine->person_id = $personId;
            $protocolLine->save();
        }

        System::setNeedRecheck();
        $url = Session::get('prev_url');
        Session::forget('prev_url');

        return redirect($url);
    }

    public function showNotIdent(Request $request): View
    {
        $search = (string)$request->get('search');

        $nameExpr = DB::raw("CONCAT(lastname, ' ',firstname) AS name");
        $lines = ProtocolLine::wherePersonId(null)
            ->select($nameExpr)
            ->groupBy('name')
            ->orderBy('name');

        if (strlen($search) > 0) {
            $lines->where('firstname', 'LIKE', '%'.$search.'%')
                ->orWhere('lastname', 'LIKE', '%'.$search.'%');
        }

        $paginator = $lines->paginate(13);
        $persons = $paginator->items();
        $names = collect($persons)->transform(fn($line) => $line->name)->toArray();

        /** @var Collection $lines */
        $lines = ProtocolLine::whereIn(DB::raw("CONCAT(lastname, ' ', firstname)"), $names)
            ->select('*', $nameExpr)
            ->with(['event.competition', 'group'])
            ->get();

        $lines = $lines->groupBy('name');

        return view('protocol-line.show-not-ident', [
            'persons' => $paginator,
            'lines' => $lines,
            'search' => $search,
        ]);
    }
}
