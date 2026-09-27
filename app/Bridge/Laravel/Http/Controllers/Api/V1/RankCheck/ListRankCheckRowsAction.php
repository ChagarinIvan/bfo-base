<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\RankCheck;

use App\Application\Dto\Pagination\Pagination;
use App\Application\Dto\RankCheck\RankCheckRowDto;
use App\Application\Dto\RankCheck\SearchRankCheckRowsDto;
use App\Application\Service\RankCheck\ListRankCheckRows;
use App\Application\Service\RankCheck\ListRankCheckRowsService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use App\Domain\Shared\Pagination\Slice;
use Illuminate\Routing\Controller as BaseController;

final class ListRankCheckRowsAction extends BaseController
{
    use ApiAction;

    /** @return Slice<RankCheckRowDto> */
    public function __invoke(
        string $rankCheckId,
        SearchRankCheckRowsDto $search,
        Pagination $pagination,
        ListRankCheckRowsService $service,
    ): Slice
    {
        return $service
            ->execute(new ListRankCheckRows((int) $rankCheckId, $search))
            ->setPerPage($pagination->perPage)
            ->setCurrentPage($pagination->page)
        ;
    }
}
