<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\Person\PersonRankHistory;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Person\ActivatePersonRankDto;
use App\Application\Dto\ProtocolLine\ViewProtocolLineDto;
use App\Application\Service\Person\ActivatePersonRank;
use App\Application\Service\Person\ActivatePersonRankService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use Illuminate\Routing\Controller as BaseController;

final class ActivatePersonRankAction extends BaseController
{
    use ApiAction;

    public function __invoke(
        string $protocolLineId,
        ActivatePersonRankDto $activation,
        ActivatePersonRankService $service,
        UserId $userId,
    ): ViewProtocolLineDto {
        return $service->execute(new ActivatePersonRank($protocolLineId, $activation, $userId));
    }
}
