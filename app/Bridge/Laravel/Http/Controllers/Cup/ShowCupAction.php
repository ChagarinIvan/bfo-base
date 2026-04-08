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

    /**
     * @url /cups/{cupId}/show
     */
    public function __invoke(
        string $cupId,
        ViewCupService $viewCupService,
        ListCupEventService $listCupEventService,
    ): View|RedirectResponse {
        try {
            $cup = $viewCupService->execute(new ViewCup($cupId));
        } catch (CupNotFound) {
            return $this->redirectTo404Error();
        }

        $events = $listCupEventService->execute(new ListCupEvent(new CupEventSearchDto(cupId: $cupId)));

        /** @see /resources/views/cup/show.blade.php */
        return $this->view('cup.show', ['cup' => $cup, 'events' => $events]);
    }

    protected function isNavbarRoute(): bool
    {
        return true;
    }
}
