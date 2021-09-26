<?php

declare(strict_types=1);

namespace App\Http\Controllers\Cups;

use App\Services\CupsService;
use App\Services\ViewActionsService;
use Illuminate\Contracts\View\View;

class ShowCupsListAction extends AbstractCupViewAction
{
    private CupsService $cupsService;

    public function __construct(ViewActionsService $viewService, CupsService $cupsService)
    {
        parent::__construct($viewService);
        $this->cupsService = $cupsService;
    }

    public function __invoke(int $year): View
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
