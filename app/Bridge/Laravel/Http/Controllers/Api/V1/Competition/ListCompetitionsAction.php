<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\Competition;

use App\Application\Dto\Competition\SearchCompetitionDto;
use App\Application\Dto\Pagination\Pagination;
use App\Application\Service\Competition\ListCompetitions;
use App\Application\Service\Competition\ListCompetitionsService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use App\Domain\Shared\Pagination\Slice;
use Illuminate\Routing\Controller as BaseController;

final class ListCompetitionsAction extends BaseController
{
    use ApiAction;

    public function __invoke(
        SearchCompetitionDto $search,
        Pagination $pagination,
        ListCompetitionsService $service,
    ): Slice
    {
        return $service
            ->execute(new ListCompetitions($search))
            ->setPerPage($pagination->perPage)
            ->setCurrentPage($pagination->page)
        ;
    }
}
