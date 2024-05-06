<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\CupEvents;

use App\Application\Dto\Event\EventSearchDto;
use App\Application\Service\Cup\Exception\CupNotFound;
use App\Application\Service\Cup\ViewCup;
use App\Application\Service\Cup\ViewCupService;
use App\Application\Service\Event\ListEvents;
use App\Application\Service\Event\ListEventsService;
use App\Bridge\Laravel\Http\Controllers\Cup\CupAction;
use App\Services\CupEventsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;
use function compact;

class ShowEditCupEventFormAction extends BaseController
{
    use CupAction;

    public function __invoke(
        string $cupId,
        string $cupEventId,
        ViewCupService $viewCupService,
        CupEventsService $cupEventsService,
        ListEventsService $listEvents,
    ): View|RedirectResponse {
        try {
            $cup = $viewCupService->execute(new ViewCup($cupId));
        } catch (CupNotFound) {
            return $this->redirectTo404Error();
        }

        $cupEvent = $cupEventsService->getCupEvent((int) $cupEventId);
        $events = $listEvents->execute(new ListEvents(new EventSearchDto(year: (string) $cup->year)));

        /** @see /resources/views/cup/events/edit.blade.php */
        return $this->view('cup.events.edit', compact('cup', 'cupEvent', 'events'));
    }

    protected function isNavbarRoute(): bool
    {
        return true;
    }
}
