<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\Person\RankAudit;

use App\Application\Dto\Pagination\Pagination;
use App\Application\Service\Person\RankAudit\ListRankAudit;
use App\Application\Service\Person\RankAudit\ListRankAuditService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use App\Domain\Shared\Pagination\Slice;
use Illuminate\Routing\Controller;

final class ListRankAuditAction extends Controller
{
    use ApiAction;

    public function __invoke(int $personId, Pagination $pagination, ListRankAuditService $service): Slice
    {
        return $service->execute(new ListRankAudit($personId))->setPerPage($pagination->perPage)->setCurrentPage($pagination->page);
    }
}
