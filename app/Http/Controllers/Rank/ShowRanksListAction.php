<?php

namespace App\Http\Controllers\Rank;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ShowRanksListAction extends AbstractRankAction
{
    public function __invoke(string $selectedRank): View|RedirectResponse
    {
        $ranks = $this->rankService->getFinishedRanks($selectedRank);

        return $this->view('ranks.index', [
            'selectedRank' => $selectedRank,
            'ranks' => $ranks->getCollection(),
        ]);
    }

    protected function isNavbarRoute(): bool
    {
        return true;
    }
}
