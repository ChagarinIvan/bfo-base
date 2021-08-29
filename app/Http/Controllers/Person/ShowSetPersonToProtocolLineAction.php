<?php

declare(strict_types=1);

namespace App\Http\Controllers\Person;

use App\Models\Club;
use App\Models\Person;
use App\Models\ProtocolLine;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ShowSetPersonToProtocolLineAction extends AbstractPersonViewAction
{
    public function __invoke(int $protocolLineId, Request $request): View
    {
        /** @var ProtocolLine $protocolLine */
        $protocolLine = ProtocolLine::find($protocolLineId);
        $search = (string)$request->get('search');
        $personsQuery = Person::with('club');

        if(strlen($search) > 0) {
            $personsQuery->where('firstname', 'LIKE', '%'.$search.'%')
                ->orWhere('lastname', 'LIKE', '%'.$search.'%')
                ->orWhere('birthday', 'LIKE', '%'.$search.'%');
        }

        $orderCase = "CASE
                    WHEN lastname LIKE '%{$protocolLine->lastname}%' THEN 0
                    WHEN firstname LIKE '%{$protocolLine->firstname}%' THEN 1
                    WHEN birthday LIKE '%{$protocolLine->year}%' THEN 3
                 ";

        $clubs = Club::whereName($protocolLine->club)->get();

        if ($clubs->count() > 0) {
            /** @var Club $club */
            $club = $clubs->first();
            $orderCase .= " WHEN club_id = {$club->id} THEN 2";
        }
        $orderCase .= ' ELSE 3 END ASC';
        $personsQuery->orderByRaw(DB::raw($orderCase));
        $persons = $personsQuery->paginate(13);

        return $this->view('protocol-line.edit-person', [
            'protocolLine' => $protocolLine,
            'persons' => $persons,
            'search' => $search,
        ]);
    }
}
