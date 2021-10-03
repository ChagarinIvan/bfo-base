<?php

declare(strict_types=1);

namespace App\Http\Controllers\Person;

use App\Models\Person;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StorePersonAction extends AbstractPersonAction
{
    public function __invoke(Request $request): RedirectResponse
    {
        $redirectUrl = (string)$request->get('redirect');
        $formParams = $request->validate([
            'lastname' => 'required',
            'firstname' => 'required',
            'birthday' => 'required|date',
            'club_id' => 'required|int',
        ]);

        $person = new Person($formParams);
        if ($person->club_id === 0) {
            $person->club_id = null;
        }
        $person->save();
        $person->makePrompts();

        return strlen($redirectUrl) > 0 ? $this->redirector->to($redirectUrl) : $this->redirector->action(ShowPersonsListAction::class);
    }
}
