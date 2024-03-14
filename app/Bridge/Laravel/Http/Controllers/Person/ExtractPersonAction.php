<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Person;

use App\Services\PersonPromptService;
use App\Services\PersonsService;
use App\Services\ProtocolLineService;
use App\Services\RankService;
use Illuminate\Http\RedirectResponse;

class ExtractPersonAction extends AbstractPersonAction
{
    public function __invoke(
        string $protocolLineId,
        ProtocolLineService $protocolLineService,
        PersonsService $personsService,
        PersonPromptService $personPromptService,
        RankService $rankService
    ): RedirectResponse {
        $protocolLine = $protocolLineService->getProtocolLine((int) $protocolLineId);
        $person = $personsService->extractPersonFromLine($protocolLine);
        $person = $personsService->storePerson($person);

        $protocolLinesToUpdate = $protocolLineService->getEqualLines($protocolLine->prepared_line);
        $oldPersons = $protocolLinesToUpdate->pluck('person_id')->unique();
        $protocolLineService->reSetPerson($protocolLinesToUpdate, $person->id);
        $personPromptService->changePromptForLine($protocolLine->prepared_line, $person->id);

        $rankService->reFillRanksForPerson($person->id);
        $oldPersons->each(static fn (int $personId) => $rankService->reFillRanksForPerson($personId));

        return $this->redirector->action(ShowPersonAction::class, [$person->id]);
    }
}
