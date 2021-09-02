<?php

declare(strict_types=1);

namespace App\Http\Controllers\Person;

use App\Models\Person;
use App\Services\RankService;
use App\Services\ViewActionsService;
use Illuminate\Contracts\View\View;

class ShowPersonRanksAction extends AbstractPersonViewAction
{
    private RankService $rankService;

    public function __construct(ViewActionsService $viewService, RankService $rankService)
    {
        parent::__construct($viewService);
        $this->rankService = $rankService;
    }

    public function __invoke(Person $person): View
    {
        $ranks = $this->rankService->getPersonRanks($person->id);

        return $this->view('ranks.show-person-ranks', [
            'ranks' => $ranks->getCollection(),
            'actualRank' => $this->rankService->getActualRank($person->id),
            'person' => $person,
        ]);
    }
}
