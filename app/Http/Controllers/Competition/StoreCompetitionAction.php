<?php

declare(strict_types=1);

namespace App\Http\Controllers\Competition;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Competition\CompetitionDto;
use App\Application\Service\Competition\AddCompetition;
use App\Application\Service\Competition\AddCompetitionService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class StoreCompetitionAction extends Controller
{
    public function __invoke(
        CompetitionDto $info,
        AddCompetitionService $service,
        UserId $userId,
    ): RedirectResponse {
        $competition = $service->execute(new AddCompetition($info, $userId));

        return $this->redirector->action(ShowCompetitionAction::class, [$competition->id]);
    }
}
