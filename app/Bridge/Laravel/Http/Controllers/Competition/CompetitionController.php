<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Competition;

use App\Application\Dto\Competition\CompetitionSearchDto;
use App\Application\Service\Competition\ListCompetitions;
use App\Application\Service\Competition\ListCompetitionsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;

final class CompetitionController extends BaseController
{
    use CompetitionAction;

    public function list(
        CompetitionSearchDto $search,
        ListCompetitionsService $service,
    ): JsonResponse {
        return new JsonResponse($service->execute(new ListCompetitions($search)));
    }
}
