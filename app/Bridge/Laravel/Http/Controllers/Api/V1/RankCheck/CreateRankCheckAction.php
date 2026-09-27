<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\RankCheck;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\RankCheck\RankCheckListDto;
use App\Application\Dto\RankCheck\ViewRankCheckDto;
use App\Application\Service\RankCheck\CreateRankCheck;
use App\Application\Service\RankCheck\CreateRankCheckService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use App\Bridge\Laravel\Http\Controllers\ResponseStatus;
use Illuminate\Routing\Controller as BaseController;

#[ResponseStatus(202)]
final class CreateRankCheckAction extends BaseController
{
    use ApiAction;

    public function __invoke(RankCheckListDto $list, CreateRankCheckService $service, UserId $userId): ViewRankCheckDto
    {
        return $service->execute(new CreateRankCheck($list, $userId));
    }
}
