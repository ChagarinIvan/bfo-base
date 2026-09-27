<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\RankCheck;

use App\Application\Dto\Pagination\Pagination;
use App\Application\Dto\RankCheck\ViewRankCheckDto;
use App\Application\Service\RankCheck\ListRankChecks;
use App\Application\Service\RankCheck\ListRankChecksService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use App\Domain\Shared\Pagination\Slice;
use Illuminate\Routing\Controller as BaseController;

final class ListRankChecksAction extends BaseController
{
    use ApiAction;

    /** @return Slice<ViewRankCheckDto> */
    public function __invoke(Pagination $pagination, ListRankChecksService $service): Slice
    {
        return $service
            ->execute(new ListRankChecks())
            ->setPerPage($pagination->perPage)
            ->setCurrentPage($pagination->page)
        ;
    }
}
