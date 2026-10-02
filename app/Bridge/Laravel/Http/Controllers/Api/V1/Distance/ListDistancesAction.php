<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\Distance;

use App\Application\Dto\Distance\DistanceSearchDto;
use App\Application\Service\Distance\ListEventDistances;
use App\Application\Service\Distance\ListEventDistancesService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use Illuminate\Routing\Controller as BaseController;

final class ListDistancesAction extends BaseController
{
    use ApiAction;

    public function __invoke(DistanceSearchDto $search, ListEventDistancesService $distances): array
    {
        return $distances->execute(new ListEventDistances($search));
    }
}
