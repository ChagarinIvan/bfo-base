<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\Person\PersonRankHistory;

use App\Application\Dto\Person\ViewPersonRankHistoryDto;
use App\Application\Service\Person\PersonRankHistory\ListPersonRankHistory;
use App\Application\Service\Person\PersonRankHistory\ListPersonRankHistoryService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use Illuminate\Routing\Controller as BaseController;

final class ListPersonRankHistoryAction extends BaseController
{
    use ApiAction;

    /** @return list<ViewPersonRankHistoryDto> */
    public function __invoke(
        string $personId,
        ListPersonRankHistoryService $service,
    ): array {
        return $service->execute(new ListPersonRankHistory($personId));
    }
}
