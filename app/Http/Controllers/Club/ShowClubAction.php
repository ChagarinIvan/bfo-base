<?php

namespace App\Http\Controllers\Club;

use App\Models\Club;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ShowClubAction extends AbstractClubAction
{
    public function __invoke(Club $club): View|RedirectResponse
    {
        return $this->view('clubs.show', [
            'club' => $club,
            'persons' => $this->personsService->getClubPersons($club->id),
        ]);
    }
}
