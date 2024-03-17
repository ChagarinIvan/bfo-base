<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Person;

use App\Application\Dto\Auth\UserId;
use App\Domain\Auth\Impression;
use App\Domain\Shared\Clock;
use App\Services\PersonPromptService;
use App\Services\PersonsService;
use App\Services\ProtocolLineService;
use App\Services\RankService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;

class ExtractPersonAction extends BaseController
{
    use PersonAction;

    public function __invoke(
        string $protocolLineId,
        ProtocolLineService $protocolLineService,
        PersonsService $personsService,
        PersonPromptService $personPromptService,
        RankService $rankService,
        UserId $userId,
        Clock $clock,
    ): RedirectResponse {
        $protocolLine = $protocolLineService->getProtocolLine((int) $protocolLineId);
        $person = $personsService->extractPersonFromLine($protocolLine, new Impression($clock->now(), $userId->id));
        $person->create();

        $protocolLinesToUpdate = $protocolLineService->getEqualLines($protocolLine->prepared_line);
        $oldPersons = $protocolLinesToUpdate->pluck('person_id')->unique();
        $protocolLineService->reSetPerson($protocolLinesToUpdate, $person->id);
        $personPromptService->changePromptForLine($protocolLine->prepared_line, $person->id);

        $rankService->reFillRanksForPerson($person->id);
        $oldPersons->each(static fn (int $personId) => $rankService->reFillRanksForPerson($personId));

        return $this->redirector->action(ShowPersonAction::class, [$person->id]);
    }
}
