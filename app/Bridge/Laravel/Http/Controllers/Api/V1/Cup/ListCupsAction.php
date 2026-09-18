<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\Cup;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Cup\SearchCupDto;
use App\Application\Dto\Pagination\Pagination;
use App\Application\Service\Cup\ListCup;
use App\Application\Service\Cup\ListCupService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use App\Domain\Shared\Pagination\Slice;
use Illuminate\Routing\Controller as BaseController;

final class ListCupsAction extends BaseController
{
    use ApiAction;

    public function __invoke(
        SearchCupDto $search,
        Pagination $pagination,
        ListCupService $service,
        ?UserId $userId = null,
    ): Slice {
        return $service
            ->execute(new ListCup($search, $userId !== null))
            ->setPerPage($pagination->perPage)
            ->setCurrentPage($pagination->page)
        ;
    }
}
