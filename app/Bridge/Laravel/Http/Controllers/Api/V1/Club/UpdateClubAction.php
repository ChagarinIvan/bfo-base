<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\Club;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Club\ClubDto;
use App\Application\Dto\Club\ViewClubDto;
use App\Application\Service\Club\UpdateClubInfo;
use App\Application\Service\Club\UpdateClubInfoService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use Illuminate\Routing\Controller as BaseController;

final class UpdateClubAction extends BaseController
{
    use ApiAction;

    public function __invoke(
        string $clubId,
        ClubDto $info,
        UpdateClubInfoService $service,
        UserId $userId,
    ): ViewClubDto {
        return $service->execute(new UpdateClubInfo($clubId, $info, $userId));
    }
}
