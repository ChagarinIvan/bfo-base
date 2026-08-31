<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\Competition;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Competition\CompetitionDto;
use App\Application\Dto\Competition\ViewCompetitionDto;
use App\Application\Service\Competition\UpdateCompetitionInfo;
use App\Application\Service\Competition\UpdateCompetitionInfoService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use Illuminate\Routing\Controller as BaseController;

final class UpdateCompetitionAction extends BaseController
{
    use ApiAction;

    public function __invoke(
        string $competitionId,
        CompetitionDto $info,
        UpdateCompetitionInfoService $service,
        UserId $userId,
    ): ViewCompetitionDto {
        return $service->execute(new UpdateCompetitionInfo($competitionId, $info, $userId));
    }
}
