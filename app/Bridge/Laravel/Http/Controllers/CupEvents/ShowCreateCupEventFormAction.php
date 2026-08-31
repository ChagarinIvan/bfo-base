<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\CupEvents;

use App\Application\Dto\Event\SearchEventDto;
use App\Application\Service\Cup\Exception\CupNotFound;
use App\Application\Service\Cup\ViewCup;
use App\Application\Service\Cup\ViewCupService;
use App\Application\Service\Event\ListEvents;
use App\Application\Service\Event\ListLegacyEventsService;
use App\Bridge\Laravel\Http\Controllers\Cup\CupAction;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;

class ShowCreateCupEventFormAction extends BaseController
{
    use CupAction;

    public function __invoke(
        string $cupId,
        ViewCupService $viewCupService,
        ListLegacyEventsService $listEvents,
    ): RedirectResponse|View {
        try {
            $cup = $viewCupService->execute(new ViewCup($cupId));
        } catch (CupNotFound) {
            return $this->redirectTo404Error();
        }

        $events = $listEvents->execute(new ListEvents(new SearchEventDto(
            year: (string) $cup->year,
            notRelatedToCup: $cupId,
        )));

        /** @see /resources/views/cup/events/create.blade.php */
        return $this->view('cup.events.create', [
            'cup' => $cup,
            'events' => $events,
        ]);
    }

    protected function isNavbarRoute(): bool
    {
        return true;
    }
}
