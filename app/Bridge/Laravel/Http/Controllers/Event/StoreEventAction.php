<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Event;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Event\EventDto;
use App\Application\Dto\Event\EventProtocolDto;
use App\Application\Service\Event\AddEvent;
use App\Application\Service\Event\AddEventService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;

class StoreEventAction extends BaseController
{
    use EventAction;

    public function __invoke(
        EventDto $eventDto,
        EventProtocolDto $protocolDto,
        AddEventService $service,
        UserId $userId,
    ): RedirectResponse {
        $event = $service->execute(new AddEvent($eventDto, $protocolDto, $userId));

        return $this->redirector->action(ShowEventAction::class, [$event->id]);
    }
}
