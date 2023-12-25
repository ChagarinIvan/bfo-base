<?php
declare(strict_types=1);

namespace App\Http\Controllers\Person;

use Illuminate\Contracts\View\View;

class ShowPersonPromptsListAction extends AbstractPersonAction
{
    public function __invoke(int $personId): View
    {
        $person = $this->personsService->getPerson($personId);

        return $this->view('persons.prompts', [
            'person' => $person,
        ]);
    }
}
