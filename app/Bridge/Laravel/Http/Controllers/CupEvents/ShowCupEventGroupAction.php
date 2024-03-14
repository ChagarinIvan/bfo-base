<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\CupEvents;

use App\Bridge\Laravel\Http\Controllers\Cups\AbstractCupAction;
use App\Models\Group\CupGroupFactory;
use App\Services\DistanceService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use RuntimeException;

class ShowCupEventGroupAction extends AbstractCupAction
{
    public function __invoke(
        string $cupId,
        string $cupEventId,
        string $cupGroupId,
        DistanceService $distanceService
    ): View|RedirectResponse {
        try {
            $group = CupGroupFactory::fromId($cupGroupId);
            $cup = $this->cupsService->getCup((int) $cupId);
            $cupEvent = $this->cupEventsService->getCupEvent((int) $cupEventId);
        } catch (RuntimeException) {
            return $this->redirectTo404Error();
        }

        $cupType = $cup->getCupType();
        $cupEventPoints = $cupType->calculateEvent($cupEvent, $group);

        return $this->view('cup.events.show', [
            'cup' => $cup,
            'cupEvent' => $cupEvent,
            'cupEventPoints' => $cupEventPoints,
            'groupId' => $group->id(),
            'clubs' => $group->id(),
        ]);
    }
}
