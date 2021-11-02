<?php

namespace App\Http\Controllers\Person;

use App\Models\Person;
use App\Models\Rank;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ShowPersonRanksAction extends AbstractPersonAction
{
    public function __invoke(Person $person): View|RedirectResponse
    {
        $ranks = $this->rankService->getPersonRanks($person->id);
        $protocolLinesIds = [];
        $ranks->each(function (Rank $rank) use (&$protocolLinesIds) {
            $protocolLinesIds[$rank->id] = $rank->event_id ? $this->protocolLinesService->getProtocolLineIdForRank($rank) : null;
        });

        return $this->view('ranks.show-person-ranks', [
            'ranks' => $ranks->getCollection(),
            'actualRank' => $this->rankService->getActualRank($person->id),
            'person' => $person,
            'protocolLinesIds' => $protocolLinesIds,
        ]);
    }
}
