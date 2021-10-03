<?php

declare(strict_types=1);

namespace App\Http\Controllers\Person;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ShowPersonsListAction extends AbstractPersonAction
{
    public function __invoke(Request $request): View
    {
        $search = (string)$request->get('search');

        $paginator = $this->personsService->getMostParticipantPersonPaginator($search);
        $items = new Collection($paginator->items());
        $persons = $this->personsService->getPersonsByIdsWithLinesAndClubs($items->pluck('id'));
        $actualRanks = [];
        foreach ($persons as $person) {
            $actualRanks[$person->id] = $this->rankService->getActualRank($person->id);
        }

        return $this->view('persons.index', [
            'paginator' => $paginator,
            'persons' => $persons,
            'actualRanks' => $actualRanks,
            'search' => $search,
        ]);
    }

    protected function isNavbarRoute(): bool
    {
        return true;
    }
}
