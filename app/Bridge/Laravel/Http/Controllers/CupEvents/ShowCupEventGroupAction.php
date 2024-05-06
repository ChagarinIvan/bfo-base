<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\CupEvents;

use App\Bridge\Laravel\Http\Controllers\Cup\CupAction;
use App\Domain\Cup\Group\CupGroupFactory;
use App\Services\CupEventsService;
use App\Services\CupsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;
use RuntimeException;

class ShowCupEventGroupAction extends BaseController
{
    use CupAction;

    public function __invoke(
        string $cupId,
        string $cupEventId,
        string $cupGroupId,
        CupsService $cupsService,
        CupEventsService $cupEventsService,
    ): View|RedirectResponse {
        try {
            $group = CupGroupFactory::fromId($cupGroupId);
            $cup = $cupsService->getCup((int) $cupId);
            $cupEvent = $cupEventsService->getCupEvent((int) $cupEventId);
        } catch (RuntimeException) {
            return $this->redirectTo404Error();
        }

        $cupTypeInstance = $cup->type->instance();
        $cupEventPoints = $cupTypeInstance->calculateEvent($cupEvent, $group);

        /** @see /resources/views/cup/events/show.blade.php */
        return $this->view('cup.events.show', [
            'cup' => $cup,
            'cupEvent' => $cupEvent,
            'cupEventPoints' => $cupEventPoints,
            'groupId' => $group->id(),
            'clubs' => $group->id(),
        ]);
    }

    protected function isNavbarRoute(): bool
    {
        return true;
    }
}
