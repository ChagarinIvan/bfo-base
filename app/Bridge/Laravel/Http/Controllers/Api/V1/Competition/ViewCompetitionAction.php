<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\Competition;

use App\Application\Dto\Competition\ViewCompetitionDto;
use App\Application\Service\Competition\ViewCompetition;
use App\Application\Service\Competition\ViewCompetitionService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use Illuminate\Routing\Controller as BaseController;

final class ViewCompetitionAction extends BaseController
{
    use ApiAction;

    public function __invoke(string $competitionId, ViewCompetitionService $service): ViewCompetitionDto
    {
        return $service->execute(new ViewCompetition($competitionId));
    }
}
