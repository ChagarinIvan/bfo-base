<?php

namespace App\Http\Controllers\Cups;

use App\Models\Year;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ShowCupsListAction extends AbstractCupAction
{
    public function __invoke(int $year): View|RedirectResponse
    {
        $selectedYear = Year::from($year);
        $cups = $this->cupsService->getYearCups($selectedYear);

        return $this->view('cup.index', compact('selectedYear', 'cups'), Year::actualYear() === $selectedYear);
    }

    protected function isNavbarRoute(): bool
    {
        return true;
    }
}
