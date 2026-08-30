<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\Auth;

use App\Application\Dto\Auth\UserId;
use App\Application\Service\Auth\Logout;
use App\Application\Service\Auth\LogoutService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use Illuminate\Routing\Controller as BaseController;
use Symfony\Component\HttpFoundation\Response;

final class LogoutAction extends BaseController
{
    use ApiAction;

    public function __invoke(UserId $userId, LogoutService $service): Response
    {
        $service->execute(new Logout($userId));

        return response()->noContent();
    }
}
