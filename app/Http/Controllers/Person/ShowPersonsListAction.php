<?php

declare(strict_types=1);

namespace App\Http\Controllers\Person;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ShowPersonsListAction extends AbstractPersonAction
{
    public function __invoke(Request $request): View
    {
        $persons = $this->personsService->allPersons();
        $actualRanks = $this->rankService->getActualRanks($persons->pluck('id'));

        return $this->view('persons.index', [
            'persons' => $persons,
            'actualRanks' => $actualRanks,
        ]);
    }

    protected function isNavbarRoute(): bool
    {
        return true;
    }
}
