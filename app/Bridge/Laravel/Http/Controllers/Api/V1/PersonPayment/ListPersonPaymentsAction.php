<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\PersonPayment;

use App\Application\Dto\Pagination\Pagination;
use App\Application\Dto\PersonPayment\SearchPersonPaymentsDto;
use App\Application\Service\PersonPayment\ListPersonsPayments;
use App\Application\Service\PersonPayment\ListPersonsPaymentsService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use App\Domain\Shared\Pagination\Slice;
use Illuminate\Routing\Controller as BaseController;

final class ListPersonPaymentsAction extends BaseController
{
    use ApiAction;

    public function __invoke(
        SearchPersonPaymentsDto $search,
        Pagination $pagination,
        ListPersonsPaymentsService $service,
    ): Slice {
        return $service
            ->paginate(new ListPersonsPayments($search))
            ->setPerPage($pagination->perPage)
            ->setCurrentPage($pagination->page)
        ;
    }
}
