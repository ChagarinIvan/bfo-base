<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Event;

use App\Application\Dto\Auth\UserId;
use App\Application\Service\Event\DisableEvent;
use App\Application\Service\Event\DisableEventService;
use App\Application\Service\Event\Exception\EventNotFound;
use App\Application\Service\Event\ViewEvent;
use App\Application\Service\Event\ViewEventService;
use App\Bridge\Laravel\Http\Controllers\Competition\ShowCompetitionAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;

class DeleteEventAction extends BaseController
{
    use EventAction;

    public function __invoke(
        string $id,
        ViewEventService $viewService,
        DisableEventService $disableService,
        UserId $userId,
    ): RedirectResponse
    {
        try {
            $event = $viewService->execute(new ViewEvent($id));
            $disableService->execute(new DisableEvent($id, $userId));
        } catch (EventNotFound) {
            return $this->redirectTo404Error();
        }

        return $this->redirector->action(ShowCompetitionAction::class, [$event->competitionId]);
    }
}
