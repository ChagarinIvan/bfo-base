<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Rank;

use App\Domain\Rank\Rank;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ShowRanksListAction extends AbstractRankAction
{
    public function __invoke(string $selectedRank): View|RedirectResponse
    {
        $ranks = $this->rankService->getFinishedRanks($selectedRank);

        /** @see /resources/views/ranks/index.blade.php */
        return $this->view('ranks.index', [
            'selectedRank' => $selectedRank,
            'ranks' => $ranks,
        ], $selectedRank === Rank::SM_RANK);
    }

    protected function isNavbarRoute(): bool
    {
        return true;
    }
}
