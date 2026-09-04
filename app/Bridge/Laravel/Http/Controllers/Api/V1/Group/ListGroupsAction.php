<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\Group;

use App\Application\Dto\Group\SearchGroupDto;
use App\Application\Dto\Pagination\Pagination;
use App\Application\Service\Group\ListGroups;
use App\Application\Service\Group\ListGroupsService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use App\Domain\Shared\Pagination\Slice;
use Illuminate\Routing\Controller as BaseController;

final class ListGroupsAction extends BaseController
{
    use ApiAction;

    public function __invoke(SearchGroupDto $search, Pagination $pagination, ListGroupsService $service): Slice
    {
        return $service->execute(new ListGroups($search))
            ->setPerPage($pagination->perPage)
            ->setCurrentPage($pagination->page)
        ;
    }
}
