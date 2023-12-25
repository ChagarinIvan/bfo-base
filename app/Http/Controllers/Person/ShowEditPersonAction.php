<?php
declare(strict_types=1);

namespace App\Http\Controllers\Person;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class ShowEditPersonAction extends AbstractPersonAction
{
    public function __invoke(int $personId): View|RedirectResponse
    {
        $person = $this->personsService->getPerson($personId);

        return $this->view('persons.edit', [
            'person' => $person,
            'clubs' => $this->clubsService->getAllClubs(),
        ]);
    }
}
