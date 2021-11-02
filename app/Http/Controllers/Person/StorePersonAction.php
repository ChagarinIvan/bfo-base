<?php

namespace App\Http\Controllers\Person;

use App\Models\Person;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StorePersonAction extends AbstractPersonAction
{
    public function __invoke(Request $request): RedirectResponse
    {
        $formParams = $request->validate([
            'lastname' => 'required',
            'firstname' => 'required',
            'birthday' => 'required|date',
            'club_id' => 'required|int',
        ]);

        $person = $this->personsService->fillPerson(new Person(), $formParams);
        $this->personsService->storePerson($person);

        return $this->redirector->action(ShowPersonsListAction::class);
    }
}
