<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\Cup;

use App\Application\Dto\Auth\UserId;
use App\Application\Service\Cup\DisableCup;
use App\Application\Service\Cup\DisableCupService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller as BaseController;

final class DeleteCupAction extends BaseController
{
    use ApiAction;

    public function __invoke(string $cupId, DisableCupService $service, UserId $userId): Response
    {
        $service->execute(new DisableCup($cupId, $userId));

        return response()->noContent();
    }
}
