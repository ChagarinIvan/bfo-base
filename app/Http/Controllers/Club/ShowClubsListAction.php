<?php

declare(strict_types=1);

namespace App\Http\Controllers\Club;

use App\Models\Club;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ShowClubsListAction extends AbstractClubAction
{
    public function __invoke(Request $request): View
    {
        return $this->view('clubs.index', ['clubs' => Club::all()]);
    }

    protected function isNavbarRoute(): bool
    {
        return true;
    }
}
