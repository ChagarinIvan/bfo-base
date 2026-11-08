<?php

declare(strict_types=1);
namespace App\Bridge\Laravel\Http\Controllers\Api\V1\Person\ManualRank;
use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Person\ManualRank\ManualPersonRankDto;
use App\Application\Dto\Person\ManualRank\ViewManualPersonRankDto;
use App\Application\Service\Person\ManualRank\AddManualPersonRank;
use App\Application\Service\Person\ManualRank\AddManualPersonRankService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use App\Bridge\Laravel\Http\Controllers\ResponseStatus;
use Illuminate\Routing\Controller as BaseController;
#[ResponseStatus(201)]
final class AddManualPersonRankAction extends BaseController
{
    use ApiAction;
    public function __invoke(int $personId, ManualPersonRankDto $dto, UserId $userId, AddManualPersonRankService $service): ViewManualPersonRankDto
    {
        return $service->execute(new AddManualPersonRank($personId, $dto, $userId));
    }
}
