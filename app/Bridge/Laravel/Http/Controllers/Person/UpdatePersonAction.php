<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Person;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UpdatePersonAction extends AbstractPersonAction
{
    public function __invoke(string $personId, Request $request): View|RedirectResponse
    {
        $formParams = $request->validate([
            'lastname' => 'required',
            'firstname' => 'required',
            'birthday' => 'required|date',
            'club_id' => 'required|int',
        ]);

        $person = $this->personsService->updatePerson((int) $personId, $formParams);

        return $this->redirector->action(ShowPersonAction::class, [$person->id]);
    }
}