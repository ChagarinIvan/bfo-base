<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Cups;

use App\Models\Year;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use function compact;

class ShowCupsListAction extends AbstractCupAction
{
    public function __invoke(string $year): View|RedirectResponse
    {
        $selectedYear = Year::from((int) $year);
        $cups = $this->cupsService->getYearCups($selectedYear);

        return $this->view('cup.index', compact('selectedYear', 'cups'), Year::actualYear() === $selectedYear);
    }

    protected function isNavbarRoute(): bool
    {
        return true;
    }
}
