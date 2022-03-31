<?php

namespace App\Http\Controllers\Cups;

use App\Models\Year;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ShowCupsListAction extends AbstractCupAction
{
    public function __invoke(int $year): View|RedirectResponse
    {
        $year = Year::from($year);
        $cups = $this->cupsService->getYearCups($year);

        return $this->view('cup.index', compact('year', 'cups'), Year::actualYear() === $year);
    }

    protected function isNavbarRoute(): bool
    {
        return true;
    }
}
