<?php

declare(strict_types=1);

namespace App\Http\Controllers\Competition;

use App\Http\Controllers\AbstractViewAction;
use App\Models\Competition;
use App\Models\Year;
use Illuminate\Contracts\View\View;

class ShowCompetitionsTableAction extends AbstractViewAction
{
    public function __invoke(int $year): View
    {
        $yearCompetitions = Competition::where('from', '>=', "{$year}-01-01")
            ->where('to', '<=', "{$year}-12-31")
            ->orderByDesc('from')
            ->get();

        return $this->viewFactory->make('competitions.index', [
            'competitions' => $yearCompetitions,
            'years' => Year::YEARS,
            'selectedYear' => $year,
        ]);
    }
}
