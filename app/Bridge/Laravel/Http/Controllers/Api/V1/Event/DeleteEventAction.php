<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\Event;

use App\Application\Dto\Auth\UserId;
use App\Application\Service\Event\DisableEvent;
use App\Application\Service\Event\DisableEventService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller as BaseController;

final class DeleteEventAction extends BaseController
{
    use ApiAction;

    public function __invoke(string $eventId, DisableEventService $service, UserId $userId): Response
    {
        $service->execute(new DisableEvent($eventId, $userId));

        return response()->noContent();
    }
}
