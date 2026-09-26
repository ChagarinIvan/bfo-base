<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\Cup;

use App\Application\Dto\Auth\UserId;
use App\Application\Service\CupEvent\DisableCupEvent;
use App\Application\Service\CupEvent\DisableCupEventService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller as BaseController;

final class DeleteCupEventAction extends BaseController
{
    use ApiAction;

    public function __invoke(string $cupEventId, DisableCupEventService $service, UserId $userId): Response
    {
        $service->execute(new DisableCupEvent($cupEventId, $userId));

        return response()->noContent();
    }
}
