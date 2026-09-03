<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\Club;

use App\Application\Dto\Club\ClubOptionDto;
use App\Application\Service\Club\ListAllClubService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use Illuminate\Routing\Controller as BaseController;

final class ListAllClubAction extends BaseController
{
    use ApiAction;

    /** @return list<ClubOptionDto> */
    public function __invoke(ListAllClubService $service): array
    {
        return $service->execute();
    }
}
