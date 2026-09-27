<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\Cup;

use App\Application\Dto\Cup\CupTableSearchDto;
use App\Application\Dto\Pagination\Pagination;
use App\Application\Service\Cup\ViewCupTable;
use App\Application\Service\Cup\ViewCupTableService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use App\Domain\Shared\Pagination\Slice;
use Illuminate\Routing\Controller as BaseController;

final class ViewCupTableAction extends BaseController
{
    use ApiAction;

    public function __invoke(
        string $cupId,
        string $groupId,
        CupTableSearchDto $search,
        Pagination $pagination,
        ViewCupTableService $service,
    ): Slice {
        return $service->execute(new ViewCupTable($cupId, $groupId, $search))
            ->setPerPage($pagination->perPage)
            ->setCurrentPage($pagination->page);
    }
}
