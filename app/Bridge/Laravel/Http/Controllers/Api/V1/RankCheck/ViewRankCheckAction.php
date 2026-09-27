<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\RankCheck;

use App\Application\Dto\RankCheck\ViewRankCheckDto;
use App\Application\Service\RankCheck\ViewRankCheck;
use App\Application\Service\RankCheck\ViewRankCheckService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use Illuminate\Routing\Controller as BaseController;

final class ViewRankCheckAction extends BaseController
{
    use ApiAction;

    public function __invoke(string $rankCheckId, ViewRankCheckService $service): ViewRankCheckDto
    {
        return $service->execute(new ViewRankCheck((int) $rankCheckId));
    }
}
