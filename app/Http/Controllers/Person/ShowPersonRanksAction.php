<?php

declare(strict_types=1);

namespace App\Http\Controllers\Person;

use App\Models\Person;
use App\Models\Rank;
use App\Services\ProtocolLineService;
use App\Services\RankService;
use App\Services\ViewActionsService;
use Illuminate\Contracts\View\View;

class ShowPersonRanksAction extends AbstractPersonViewAction
{
    private RankService $rankService;
    private ProtocolLineService $protocolLinesService;

    public function __construct(ViewActionsService $viewService, RankService $rankService, ProtocolLineService $protocolLinesService)
    {
        parent::__construct($viewService);
        $this->rankService = $rankService;
        $this->protocolLinesService = $protocolLinesService;
    }

    public function __invoke(Person $person): View
    {
        $ranks = $this->rankService->getPersonRanks($person->id);
        $protocolLinesIds = [];
        $ranks->each(function (Rank $rank) use (&$protocolLinesIds) {
            $protocolLinesIds[$rank->id] = $this->protocolLinesService->getProtocolLineIdForRank($rank);
        });

        return $this->view('ranks.show-person-ranks', [
            'ranks' => $ranks->getCollection(),
            'actualRank' => $this->rankService->getActualRank($person->id),
            'person' => $person,
            'protocolLinesIds' => $protocolLinesIds,
        ]);
    }
}
