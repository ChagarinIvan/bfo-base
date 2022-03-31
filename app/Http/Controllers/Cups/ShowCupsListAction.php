<?php

namespace App\Http\Controllers\Cups;

use App\Models\Year;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ShowCupsListAction extends AbstractCupAction
{
    public function __invoke(Year $year): View|RedirectResponse
    {
        $cups = $this->cupsService->getYearCups($year);

        return $this->view('cup.index', [
            'cups' => $cups,
            'selectedYear' => $year,
        ], Year::actualYear() === $year);
    }

    protected function isNavbarRoute(): bool
    {
        return true;
    }
}
