<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\Auth;

use App\Application\Dto\Auth\UserDto;
use App\Application\Service\Auth\ListUsersService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use Illuminate\Routing\Controller as BaseController;

final class ListUsersAction extends BaseController
{
    use ApiAction;

    /** @return UserDto[] */
    public function __invoke(ListUsersService $service): array
    {
        return $service->execute();
    }
}
