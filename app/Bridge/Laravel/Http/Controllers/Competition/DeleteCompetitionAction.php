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

    public function __invoke(
        string $year,
        string $id,
        DisableCompetitionService $service,
        UserId $userId,
    ): RedirectResponse {
        try {
            $service->execute(new DisableCompetition($id, $userId));
        } catch (CompetitionNotFound) {
            return $this->redirectTo404Error();
        }

        return $this->redirector->action(ShowCompetitionsListAction::class, ['year' => $year]);
    }
}
