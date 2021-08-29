<?php

declare(strict_types=1);

namespace App\Http\Controllers\Person;

use App\Http\Controllers\AbstractRedirectAction;
use App\Models\Person;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UpdatePersonAction extends AbstractRedirectAction
{
    public function __invoke(Person $person, Request $request): RedirectResponse
    {
        $formParams = $request->validate([
            'lastname' => 'required',
            'firstname' => 'required',
            'birthday' => 'required|date',
            'club_id' => 'required|int',
        ]);

        $person->fill($formParams);
        if ($person->club_id === 0) {
            $person->club_id = null;
        }

        $person->save();
        $person->makePrompts();

        return $this->redirector->action(ShowPersonsListAction::class);
    }
}
