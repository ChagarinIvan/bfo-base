<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\Competition;

use App\Application\Dto\Competition\CompetitionSearchDto;
use App\Application\Dto\Competition\ViewCompetitionDto;
use App\Application\Service\Competition\ListCompetitions;
use App\Application\Service\Competition\ListCompetitionsService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use Illuminate\Routing\Controller as BaseController;

final class ListCompetitionsAction extends BaseController
{
    use ApiAction;

    /** @return ViewCompetitionDto[] */
    public function __invoke(CompetitionSearchDto $search, ListCompetitionsService $service): array
    {
        return $service->execute(new ListCompetitions($search));
    }
}
