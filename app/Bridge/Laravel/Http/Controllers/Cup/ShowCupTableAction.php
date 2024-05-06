<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Cup;

use App\Domain\Cup\Cup;
use App\Domain\Cup\Group\CupGroupFactory;
use App\Domain\Person\Person;
use App\Services\CupEventsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;
use function array_keys;

class ShowCupTableAction extends BaseController
{
    use CupAction;

    public function __invoke(Cup $cup, string $cupGroupId, CupEventsService $service): View|RedirectResponse
    {
        $cupEvents = $service->getCupEvents($cup)->sortBy('event.date');
        $cupGroup = CupGroupFactory::fromId($cupGroupId);
        $cupPoints = $service->calculateCup($cup, $cupEvents, $cupGroup);

        return $this->view('cup.table', [
            'cup' => $cup,
            'cupEvents' => $cupEvents,
            'cupPoints' => $cupPoints,
            'persons' => Person::whereIn('id', array_keys($cupPoints))->get()->keyBy('id'),
            'activeGroup' => $cupGroup,
        ]);
    }

    protected function isNavbarRoute(): bool
    {
        return true;
    }
}
