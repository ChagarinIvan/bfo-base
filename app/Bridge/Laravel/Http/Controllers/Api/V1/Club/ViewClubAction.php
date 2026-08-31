<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\Club;

use App\Application\Dto\Club\ViewClubDto;
use App\Application\Service\Club\ViewClub;
use App\Application\Service\Club\ViewClubService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use Illuminate\Routing\Controller as BaseController;

final class ViewClubAction extends BaseController
{
    use ApiAction;

    public function __invoke(string $clubId, ViewClubService $service): ViewClubDto
    {
        return $service->execute(new ViewClub($clubId));
    }
}
