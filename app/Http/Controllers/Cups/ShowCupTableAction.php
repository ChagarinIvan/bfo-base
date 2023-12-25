<?php
declare(strict_types=1);

namespace App\Http\Controllers\Cups;

use App\Models\Cup;
use App\Models\Group;
use App\Models\Person;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use function array_keys;

class ShowCupTableAction extends AbstractCupAction
{
    public function __invoke(Cup $cup, string $cupGroupId): View|RedirectResponse
    {
        $cupEvents = $this->cupEventsService->getCupEvents($cup)->sortBy('event.date');
        $cupGroup = Group\CupGroupFactory::fromId($cupGroupId);
        $cupPoints = $this->cupEventsService->calculateCup($cup, $cupEvents, $cupGroup);

        return $this->view('cup.table', [
            'cup' => $cup,
            'cupEvents' => $cupEvents,
            'cupPoints' => $cupPoints,
            'persons' => Person::whereIn('id', array_keys($cupPoints))->get()->keyBy('id'),
            'activeGroup' => $cupGroup,
        ]);
    }
}
