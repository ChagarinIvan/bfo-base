<?php

declare(strict_types=1);

namespace App\Http\Controllers\Person;

use App\Services\PersonsService;
use App\Services\RankService;
use App\Services\ViewActionsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ShowPersonsListAction extends AbstractPersonViewAction
{
    private PersonsService $personsService;
    private RankService $rankService;

    public function __construct(ViewActionsService $viewService, PersonsService $personsService, RankService $rankService)
    {
        parent::__construct($viewService);
        $this->personsService = $personsService;
        $this->rankService = $rankService;
    }

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
