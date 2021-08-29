<?php

declare(strict_types=1);

namespace App\Http\Controllers\Person;

use App\Http\Controllers\AbstractRedirectAction;
use App\Models\Person;
use App\Models\ProtocolLine;
use Illuminate\Http\RedirectResponse;

class DeletePersonAction extends AbstractRedirectAction
{
    public function __invoke(Person $person): RedirectResponse
    {
        $protocolLines = ProtocolLine::wherePersonId($person->id)->get();
        $protocolLines->each(function (ProtocolLine $line) {
            $line->person_id = null;
            $line->save();
        });
        $person->delete();
        return $this->redirector->action(ShowPersonsListAction::class);
    }
}
