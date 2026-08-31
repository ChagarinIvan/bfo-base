<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\Competition;

use App\Application\Dto\Auth\UserId;
use App\Application\Service\Competition\DisableCompetition;
use App\Application\Service\Competition\DisableCompetitionService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller as BaseController;

final class DeleteCompetitionAction extends BaseController
{
    use ApiAction;

    public function __invoke(
        string $competitionId,
        DisableCompetitionService $service,
        UserId $userId,
    ): Response {
        $service->execute(new DisableCompetition($competitionId, $userId));

        return response()->noContent();
    }
}
