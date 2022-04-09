<?php

namespace App\Http\Controllers\Person;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class DeletePersonAction extends AbstractPersonAction
{
    public function __invoke(int $personId): View|RedirectResponse
    {
        $this->personsService->deletePerson($personId);

        return $this->redirector->action(ShowPersonsListAction::class);
    }
}
