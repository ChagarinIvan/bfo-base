<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Api\V1\Event;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Event\EventDto;
use App\Application\Dto\Event\EventProtocolDto;
use App\Application\Dto\Event\ViewEventDto;
use App\Application\Service\Event\AddEvent;
use App\Application\Service\Event\AddEventService;
use App\Bridge\Laravel\Http\Controllers\ApiAction;
use App\Bridge\Laravel\Http\Controllers\ResponseStatus;
use Illuminate\Routing\Controller as BaseController;

#[ResponseStatus(201)]
final class CreateEventAction extends BaseController
{
    use ApiAction;

    public function __invoke(
        int $competitionId,
        EventDto $event,
        EventProtocolDto $protocol,
        AddEventService $service,
        UserId $userId,
    ): ViewEventDto {
        return $service->execute(new AddEvent($competitionId, $event, $protocol, $userId));
    }
}
