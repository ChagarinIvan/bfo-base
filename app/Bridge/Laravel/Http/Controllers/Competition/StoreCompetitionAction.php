<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Competition;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Competition\CompetitionDto;
use App\Application\Service\Competition\AddCompetition;
use App\Application\Service\Competition\AddCompetitionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;

final class StoreCompetitionAction extends BaseController
{
    use CompetitionAction;

    public function __invoke(
        CompetitionDto $info,
        AddCompetitionService $service,
        UserId $userId,
    ): RedirectResponse {
        $competition = $service->execute(new AddCompetition($info, $userId));

        return $this->redirector->action(ShowCompetitionAction::class, [$competition->id]);
    }
}
