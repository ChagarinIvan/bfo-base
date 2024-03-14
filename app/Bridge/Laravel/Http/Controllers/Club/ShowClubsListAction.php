<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Club;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ShowClubsListAction extends AbstractClubAction
{
    public function __invoke(): View|RedirectResponse
    {
        return $this->view('clubs.index', ['clubs' => $this->clubsService->getAllClubs()]);
    }

    protected function isNavbarRoute(): bool
    {
        return true;
    }
}
