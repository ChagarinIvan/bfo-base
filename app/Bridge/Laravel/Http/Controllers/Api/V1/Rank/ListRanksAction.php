<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\Rank;

use App\Application\Service\Rank\ListRanks;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use Illuminate\Routing\Controller as BaseController;

final class ListRanksAction extends BaseController
{
    use ApiAction;

    /** @return list<array{id: string, label: string}> */
    public function __invoke(ListRanks $listRanks): array
    {
        return $listRanks->execute();
    }
}
