<?php

declare(strict_types=1);

namespace App\Http\Controllers\Person;

use App\Models\Person;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ShowPersonsListAction extends AbstractPersonViewAction
{
    public function __invoke(Request $request): View
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
        $items = new Collection($paginator->items());
        $persons = Person::with(['protocolLines', 'club'])->find($items->pluck('id'));

        return $this->view('persons.index', [
            'paginator' => $paginator,
            'persons' => $persons,
            'search' => $search,
        ]);
    }
}
