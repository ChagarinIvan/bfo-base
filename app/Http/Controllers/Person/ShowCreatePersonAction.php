<?php

namespace App\Http\Controllers\Person;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ShowCreatePersonAction extends AbstractPersonAction
{
    public function __invoke(): View|RedirectResponse
    {
        return $this->view('persons.create', [
            'clubs' => $this->clubsService->getAllClubs(),
        ]);
    }
}
