<?php
declare(strict_types=1);

namespace App\Http\Controllers\Rank;

use App\Models\Rank;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ShowRanksListAction extends AbstractRankAction
{
    public function __invoke(string $selectedRank): View|RedirectResponse
    {
        $ranks = $this->rankService->getFinishedRanks($selectedRank);

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
