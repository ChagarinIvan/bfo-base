<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Competition;

use App\Application\Dto\Auth\UserId;
use App\Application\Service\Competition\DisableCompetition;
use App\Application\Service\Competition\DisableCompetitionService;
use App\Application\Service\Competition\Exception\CompetitionNotFound;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;

final class DeleteCompetitionAction extends BaseController
{
    use CompetitionAction;

    /**
     * @url /competitions/{competitionId}/delete
     */
    public function __invoke(
        string $competitionId,
        DisableCompetitionService $service,
        UserId $userId,
    ): RedirectResponse {
        try {
            $competition = $service->execute(new DisableCompetition($competitionId, $userId));
        } catch (CompetitionNotFound) {
            return $this->redirectTo404Error();
        }

        return $this->redirector->action(ShowCompetitionsListAction::class, ['year' => $competition->year]);
    }
}
