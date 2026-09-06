<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\Person\PersonRankHistory;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Person\UpdatePersonRankActivationDateDto;
use App\Application\Dto\ProtocolLine\ViewProtocolLineDto;
use App\Application\Service\Person\UpdatePersonRankActivationDate;
use App\Application\Service\Person\UpdatePersonRankActivationDateService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use Illuminate\Routing\Controller as BaseController;

final class UpdatePersonRankActivationAction extends BaseController
{
    use ApiAction;

    public function __invoke(
        string $protocolLineId,
        UpdatePersonRankActivationDateDto $activation,
        UpdatePersonRankActivationDateService $service,
        UserId $userId,
    ): ViewProtocolLineDto {
        return $service->execute(new UpdatePersonRankActivationDate($protocolLineId, $activation, $userId));
    }
}
