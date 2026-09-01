<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\Club;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Club\ClubDto;
use App\Application\Dto\Club\ViewClubDto;
use App\Application\Service\Club\AddClub;
use App\Application\Service\Club\AddClubService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use App\Bridge\Laravel\Http\Controllers\ResponseStatus;
use Illuminate\Routing\Controller as BaseController;

#[ResponseStatus(201)]
final class CreateClubAction extends BaseController
{
    use ApiAction;

    public function __invoke(ClubDto $info, AddClubService $service, UserId $userId): ViewClubDto
    {
        return $service->execute(new AddClub($info, $userId));
    }
}
