<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\Auth;

use App\Application\Dto\Auth\UserId;
use App\Application\Service\Auth\StartHorizonSession;
use App\Application\Service\Auth\StartHorizonSessionService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use Illuminate\Routing\Controller as BaseController;
use Symfony\Component\HttpFoundation\Response;

final class StartHorizonSessionAction extends BaseController
{
    use ApiAction;

    public function __invoke(UserId $userId, StartHorizonSessionService $service): Response
    {
        $service->execute(new StartHorizonSession($userId));

        return response()->noContent();
    }
}
