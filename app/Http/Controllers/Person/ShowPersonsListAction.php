<?php

namespace App\Http\Controllers\Person;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ShowPersonsListAction extends AbstractPersonAction
{
    public function __invoke(Request $request): View|RedirectResponse
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
