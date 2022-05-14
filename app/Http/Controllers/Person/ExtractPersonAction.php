<?php

namespace App\Http\Controllers\Person;

use App\Services\PersonPromptService;
use App\Services\PersonsService;
use App\Services\ProtocolLineService;
use App\Services\RankService;
use Illuminate\Http\RedirectResponse;

class ExtractPersonAction extends AbstractPersonAction
{
    public function __invoke(
        int $protocolLineId,
        ProtocolLineService $protocolLineService,
        PersonsService $personsService,
        PersonPromptService $personPromptService,
        RankService $rankService
    ): RedirectResponse {
        $protocolLine = $protocolLineService->getProtocolLine($protocolLineId);
        $person = $personsService->extractPersonFromLine($protocolLine);
        $person = $personsService->storePerson($person);

        $protocolLinesToUpdate = $protocolLineService->getEqualLines($protocolLine->prepared_line);
        $protocolLinesToUpdate = $protocolLineService->reSetPerson($protocolLinesToUpdate, $person->id);
        $rankService->replaceRanksToPerson($protocolLinesToUpdate, $person->id);
        $personPromptService->changePromptForLine($protocolLine->prepared_line, $person->id);

        return $this->redirector->action(ShowPersonAction::class, [$person->id]);
    }
}
