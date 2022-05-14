<?php

namespace App\Http\Controllers\Person;

use App\Services\PersonsService;
use App\Services\ProtocolLineService;
use Illuminate\Http\RedirectResponse;

class ExtractPersonAction extends AbstractPersonAction
{
    public function __invoke(
        int $protocolLineId,
        ProtocolLineService $protocolLineService,
        PersonsService $personsService
    ): RedirectResponse {
        $protocolLine = $protocolLineService->getProtocolLine($protocolLineId);
        $person = $personsService->extractPersonFromLine($protocolLine);
        $person = $personsService->storePerson($person);

        return $this->redirector->action(ShowPersonAction::class, [$person->id]);
    }
}
