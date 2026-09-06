<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\ProtocolLine;

use App\Application\Dto\Pagination\Pagination;
use App\Application\Dto\ProtocolLine\SearchProtocolLineDto;
use App\Application\Dto\ProtocolLine\ViewProtocolLineDto;
use App\Application\Service\ProtocolLine\ListProtocolLines;
use App\Application\Service\ProtocolLine\ListProtocolLinesService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use App\Domain\Shared\Pagination\Slice;
use Illuminate\Routing\Controller as BaseController;

final class ListProtocolLinesAction extends BaseController
{
    use ApiAction;

    /** @return Slice<ViewProtocolLineDto> */
    public function __invoke(
        SearchProtocolLineDto $search,
        Pagination $pagination,
        ListProtocolLinesService $service,
    ): Slice {
        return $service
            ->execute(new ListProtocolLines($search))
            ->setPerPage($pagination->perPage)
            ->setCurrentPage($pagination->page)
        ;
    }
}
