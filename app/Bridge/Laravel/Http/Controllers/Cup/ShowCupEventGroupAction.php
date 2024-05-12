<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Cup;

use App\Application\Service\Cup\CalculateCupEvent;
use App\Application\Service\Cup\CalculateCupEventService;
use App\Application\Service\Cup\Exception\CupNotFound;
use App\Application\Service\CupEvent\Exception\CupEventNotFound;
use App\Application\Service\Group\Exception\GroupNotFound;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;

class ShowCupEventGroupAction extends BaseController
{
    use CupAction;

    public function __invoke(
        string $cupId,
        string $cupEventId,
        string $groupId,
        CalculateCupEventService $service,
    ): View|RedirectResponse {
        try {
            $calculatedCupEvent = $service->execute(new CalculateCupEvent($cupId, $cupEventId, $groupId));
        } catch (CupNotFound|GroupNotFound|CupEventNotFound) {
            return $this->redirectTo404Error();
        }

        /** @see /resources/views/cup/events/show.blade.php */
        return $this->view('cup.events.show', [
            'calculatedCupEvent' => $calculatedCupEvent,
            'groupId' => $groupId,
        ]);
    }

    protected function isNavbarRoute(): bool
    {
        return true;
    }
}
