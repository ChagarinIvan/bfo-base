<?php

declare(strict_types=1);

namespace App\Http\Controllers\Rank;

use App\Http\Controllers\AbstractViewAction;
use App\Services\RankService;
use App\Services\ViewActionsService;
use Illuminate\Contracts\View\View;

class ShowRanksListAction extends AbstractViewAction
{
    private RankService $rankService;

    public function __construct(ViewActionsService $viewService, RankService $rankService)
    {
        parent::__construct($viewService);
        $this->rankService = $rankService;
    }

    public function __invoke(string $selectedRank): View
    {
        $ranks = $this->rankService->getFinishedRanks($selectedRank);

        return $this->view('ranks.index', [
            'selectedRank' => $selectedRank,
            'ranks' => $ranks->getCollection(),
        ]);
    }

    protected function isRanksRoute(): bool
    {
        return false;
    }

    protected function isNavbarRoute(): bool
    {
        return true;
    }
}