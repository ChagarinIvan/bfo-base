<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Person;

use App\Models\Person;
use App\Models\ProtocolLine;
use App\Services\PersonPromptService;
use App\Services\ProtocolLineService;
use App\Services\RankService;
use Illuminate\Http\RedirectResponse;

class SetProtocolLinePersonAction extends AbstractPersonAction
{
    public function __invoke(
        Person $person,
        string $protocolLineId,
        ProtocolLineService $protocolLineService,
        PersonPromptService $personPromptService,
        RankService $rankService
    ): RedirectResponse {
        /** @var ProtocolLine $protocolLine */
        $protocolLine = ProtocolLine::find($protocolLineId);
        $oldPersonId = $protocolLine->person_id;
        $preparedLine = $protocolLine->prepared_line;

        //сохраняем результат для всех строчек с установленным идентификатором
        $protocolLinesToUpdate = $protocolLineService->getEqualLines($preparedLine);
        $oldPersons = $protocolLinesToUpdate
            ->pluck('person_id')
            ->unique()
            ->filter(static fn ($personId) => $personId !== null)
        ;

        $protocolLineService->reSetPerson($protocolLinesToUpdate, $person->id);
        $personPromptService->changePromptForLine($preparedLine, $person->id);

        $rankService->reFillRanksForPerson($person->id);
        $oldPersons->each(static fn (int $personId) => $rankService->reFillRanksForPerson($personId));

        if (ProtocolLine::wherePersonId($oldPersonId)->count() === 0) {
            Person::destroy($oldPersonId);
        };

        return $this->redirector->to($this->removeLastBackUrl());
    }
}
