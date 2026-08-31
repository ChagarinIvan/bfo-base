<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\Club;

use App\Application\Dto\Club\SearchClubDto;
use App\Application\Dto\Pagination\Pagination;
use App\Application\Service\Club\ListClubs;
use App\Application\Service\Club\ListClubsService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use App\Domain\Shared\Pagination\Slice;
use Illuminate\Routing\Controller as BaseController;

final class ListClubsAction extends BaseController
{
    use ApiAction;

    public function __invoke(
        SearchClubDto $search,
        Pagination $pagination,
        ListClubsService $service,
    ): Slice {
        return $service
            ->execute(new ListClubs($search))
            ->setPerPage($pagination->perPage)
            ->setCurrentPage($pagination->page)
        ;
    }
}
