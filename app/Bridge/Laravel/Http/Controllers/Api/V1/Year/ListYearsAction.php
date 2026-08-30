<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\Year;

use App\Application\Service\Year\ListYears;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use Illuminate\Routing\Controller as BaseController;

final class ListYearsAction extends BaseController
{
    use ApiAction;

    /** @return list<int> */
    public function __invoke(ListYears $listYears): array
    {
        return $listYears->execute();
    }
}
