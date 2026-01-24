<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Event;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Event\UpdateEventDto;
use App\Application\Service\Event\UpdateEvent;
use App\Application\Service\Event\UpdateEventService;
use App\Bridge\Laravel\Http\Controllers\Competition\ShowCompetitionAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;

class UpdateEventAction extends BaseController
{
    use EventAction;

    public function __invoke(
        string $id,
        UpdateEventDto $eventDto,
        UpdateEventService $service,
        UserId $userId,
    ): RedirectResponse {
        $event = $service->execute(new UpdateEvent($id, $eventDto, $userId));

        return $this->redirector->action(ShowCompetitionAction::class, [$event->competitionId]);
    }
}
