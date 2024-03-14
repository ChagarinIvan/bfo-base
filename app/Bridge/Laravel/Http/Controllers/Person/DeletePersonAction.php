<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Person;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class DeletePersonAction extends AbstractPersonAction
{
    public function __invoke(string $personId): View|RedirectResponse
    {
        $this->personsService->deletePerson((int) $personId);

        return $this->redirector->action(ShowPersonsListAction::class);
    }
}
