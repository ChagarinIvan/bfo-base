<?php

declare(strict_types=1);

namespace App\Http\Controllers\Cups;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ShowCupsListAction extends AbstractCupAction
{
    public function __invoke(int $year): View|RedirectResponse
    {
        $cups = $this->cupsService->getYearCups($year);

        return $this->view('cup.index', [
            'cups' => $cups,
            'selectedYear' => $year,
        ]);
    }

    protected function isNavbarRoute(): bool
    {
        return true;
    }
}
