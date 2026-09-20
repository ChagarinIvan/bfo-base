<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\Cup;

use App\Application\Dto\CupEvent\CupEventSearchDto;
use App\Application\Dto\Pagination\Pagination;
use App\Application\Service\CupEvent\ListCupEvent;
use App\Application\Service\CupEvent\ListCupEventService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use App\Domain\Shared\Pagination\Slice;
use Illuminate\Routing\Controller as BaseController;

final class ListCupEventsAction extends BaseController
{
    use ApiAction;

    public function __invoke(CupEventSearchDto $search, Pagination $pagination, ListCupEventService $service): Slice
    {
        return $service
            ->execute(new ListCupEvent($search))
            ->setPerPage($pagination->perPage)
            ->setCurrentPage($pagination->page)
        ;
    }
}
