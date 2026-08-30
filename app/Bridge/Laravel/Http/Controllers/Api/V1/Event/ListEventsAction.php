<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\Event;

use App\Application\Dto\Event\SearchEventDto;
use App\Application\Dto\Pagination\Pagination;
use App\Application\Service\Event\ListEvents;
use App\Application\Service\Event\ListEventsService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use App\Domain\Shared\Pagination\Slice;
use Illuminate\Routing\Controller as BaseController;

final class ListEventsAction extends BaseController
{
    use ApiAction;

    public function __invoke(
        SearchEventDto $search,
        Pagination $pagination,
        ListEventsService $events,
    ): Slice {
        return $events
            ->execute(new ListEvents($search))
            ->setPerPage($pagination->perPage)
            ->setCurrentPage($pagination->page)
        ;
    }
}
