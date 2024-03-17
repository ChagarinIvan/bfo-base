<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Rank;

use App\Domain\Person\Person;
use App\Models\Rank;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ShowPersonRanksAction extends AbstractRankAction
{
    public function __invoke(Person $person): View|RedirectResponse
    {
        $ranks = $this->rankService->getPersonRanks($person->id);
        $protocolLinesIds = [];
        $ranks->each(function (Rank $rank) use (&$protocolLinesIds): void {
            $protocolLinesIds[$rank->id] = $rank->event_id ? $this->protocolLinesService->getProtocolLineIdForRank($rank) : null;
        });

        return $this->view('ranks.show-person-ranks', [
            'ranks' => $ranks,
            'actualRank' => $this->rankService->getActiveRank($person->id),
            'person' => $person,
            'protocolLinesIds' => $protocolLinesIds,
        ]);
    }
}
