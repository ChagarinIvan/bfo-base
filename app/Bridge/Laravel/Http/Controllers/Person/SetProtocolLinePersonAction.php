<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Person;

use App\Application\Dto\Auth\UserId;
use App\Application\Service\Person\DisablePerson;
use App\Application\Service\Person\DisablePersonService;
use App\Application\Service\Person\RebuildPersonRanks;
use App\Application\Service\Person\RebuildPersonRanksService;
use App\Application\Service\PersonPrompt\ChangePersonPrompt;
use App\Application\Service\PersonPrompt\ChangePersonPromptService;
use App\Domain\Person\Person;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Services\ProtocolLineService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;

class SetProtocolLinePersonAction extends BaseController
{
    use PersonAction;

    public function __invoke(
        Person $person,
        string $protocolLineId,
        UserId $userId,
        ProtocolLineService $protocolLineService,
        ChangePersonPromptService $personPromptService,
        RebuildPersonRanksService $rebuildPersonRanksService,
        DisablePersonService $disablePersonService,
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
            ->filter(static fn ($personId): bool => $personId !== null)
        ;

        $protocolLineService->reSetPerson($protocolLinesToUpdate, $person->id);
        $personPromptService->execute(new ChangePersonPrompt($preparedLine, $person->id, $userId));

        $rebuildPersonRanksService->execute(new RebuildPersonRanks($person->id, $userId));
        $oldPersons->each(static fn (int $personId) => $rebuildPersonRanksService->execute(new RebuildPersonRanks($personId, $userId)));

        if (ProtocolLine::wherePersonId($oldPersonId)->count() === 0) {
            $disablePersonService->execute(new DisablePerson((string)$oldPersonId, $userId));
        }

        return $this->redirector->back();
    }
}
