<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\Person;

use App\Application\Dto\Pagination\Pagination;
use App\Application\Dto\Person\SearchPersonDto;
use App\Application\Service\Person\ListPersons;
use App\Application\Service\Person\ListPersonsService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use App\Domain\Shared\Pagination\Slice;
use Illuminate\Routing\Controller as BaseController;

final class ListPersonsAction extends BaseController
{
    use ApiAction;

    public function __invoke(
        SearchPersonDto $search,
        Pagination $pagination,
        ListPersonsService $service,
    ): Slice {
        return $service
            ->execute(new ListPersons($search))
            ->setPerPage($pagination->perPage)
            ->setCurrentPage($pagination->page)
        ;
    }
}
