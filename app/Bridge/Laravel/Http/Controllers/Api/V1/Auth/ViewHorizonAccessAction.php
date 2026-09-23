<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\Auth;

use App\Application\Dto\Auth\HorizonAccessDto;
use App\Application\Dto\Auth\UserId;
use App\Application\Service\Auth\ViewHorizonAccess;
use App\Application\Service\Auth\ViewHorizonAccessService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use Illuminate\Routing\Controller as BaseController;

final class ViewHorizonAccessAction extends BaseController
{
    use ApiAction;

    public function __invoke(UserId $userId, ViewHorizonAccessService $service): HorizonAccessDto
    {
        return $service->execute(new ViewHorizonAccess($userId));
    }
}
