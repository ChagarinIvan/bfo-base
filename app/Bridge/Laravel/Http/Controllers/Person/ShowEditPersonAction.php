<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Person;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ShowEditPersonAction extends AbstractPersonAction
{
    public function __invoke(string $personId): View|RedirectResponse
    {
        $person = $this->personsService->getPerson((int) $personId);

        return $this->view('persons.edit', [
            'person' => $person,
            'clubs' => $this->clubsService->getAllClubs(),
        ]);
    }
}
