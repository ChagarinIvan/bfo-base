<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Cup;

use App\Application\Dto\CupEvent\CupEventSearchDto;
use App\Application\Service\Cup\Exception\CupNotFound;
use App\Application\Service\Cup\ViewCup;
use App\Application\Service\Cup\ViewCupService;
use App\Application\Service\CupEvent\ListCupEvent;
use App\Application\Service\CupEvent\ListCupEventService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;

class ShowCupAction extends BaseController
{
    use CupAction;

    public function __invoke(
        string $cupId,
        ViewCupService $cupService,
        ListCupEventService $cupEventsService,
    ): View|RedirectResponse {
        try {
            $cup = $cupService->execute(new ViewCup($cupId));
        } catch (CupNotFound) {
            return $this->redirectTo404Error();
        }

        $cupEvents = $cupEventsService->execute(new ListCupEvent(new CupEventSearchDto(cupId: $cupId)));

        /** @see /resources/views/cup/show.blade.php */
        return $this->view('cup.show', ['cup' => $cup, 'cupEvents' => $cupEvents]);
    }

    protected function isNavbarRoute(): bool
    {
        return true;
    }
}
