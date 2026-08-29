<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\Competition;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Competition\CompetitionDto;
use App\Application\Dto\Competition\ViewCompetitionDto;
use App\Application\Service\Competition\AddCompetition;
use App\Application\Service\Competition\AddCompetitionService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use Illuminate\Routing\Controller as BaseController;

final class CreateCompetitionAction extends BaseController
{
    use ApiAction;

    public function __invoke(
        CompetitionDto $info,
        AddCompetitionService $service,
        UserId $userId
    ): ViewCompetitionDto {
        return $service->execute(new AddCompetition($info, $userId));
    }
}
