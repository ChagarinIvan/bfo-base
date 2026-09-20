<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\Cup;

use App\Application\Dto\CupEvent\CupEventPointSearchDto;
use App\Application\Dto\Pagination\Pagination;
use App\Application\Service\CupEvent\ListCupEventPoints;
use App\Application\Service\CupEvent\ListCupEventPointsService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use App\Domain\Shared\Pagination\Slice;
use Illuminate\Routing\Controller as BaseController;

final class ListCupEventPointsAction extends BaseController
{
    use ApiAction;

    public function __invoke(
        string $cupEventId,
        CupEventPointSearchDto $search,
        Pagination $pagination,
        ListCupEventPointsService $service,
    ): Slice {
        return $service
            ->execute(new ListCupEventPoints($cupEventId, $search))
            ->setPerPage($pagination->perPage)
            ->setCurrentPage($pagination->page)
        ;
    }
}
