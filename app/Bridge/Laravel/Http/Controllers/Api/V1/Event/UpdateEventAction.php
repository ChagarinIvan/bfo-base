<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\Event;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Event\UpdateEventDto;
use App\Application\Dto\Event\ViewEventDto;
use App\Application\Service\Event\UpdateEvent;
use App\Application\Service\Event\UpdateEventService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use Illuminate\Routing\Controller as BaseController;

final class UpdateEventAction extends BaseController
{
    use ApiAction;

    public function __invoke(
        string $eventId,
        UpdateEventDto $event,
        UpdateEventService $service,
        UserId $userId,
    ): ViewEventDto {
        return $service->execute(new UpdateEvent($eventId, $event, $userId));
    }
}
