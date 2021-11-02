<?php

namespace App\Http\Controllers\Person;

use App\Models\Person;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UpdatePersonAction extends AbstractPersonAction
{
    public function __invoke(Person $person, Request $request): RedirectResponse
    {
        $formParams = $request->validate([
            'lastname' => 'required',
            'firstname' => 'required',
            'birthday' => 'required|date',
            'club_id' => 'required|int',
        ]);

        $this->personsService->updatePerson($person, $formParams);

        return $this->redirector->action(ShowPersonsListAction::class);
    }
}
