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
use Illuminate\Support\Collection;
use function array_keys;
use function preg_match;

class ShowCupTableAction extends BaseController
{
    use CupAction;

    public function __invoke(Cup $cup, string $cupGroupId, CupEventsService $service): View|RedirectResponse
    {
        // fix wrong group
        if (preg_match('#^(\D)_(\d+)$#', $cupGroupId)) {
            $cupGroupId .= '_';
        }

        $cupEvents = $service->getCupEvents((string) $cup->id)->sortBy('event.date');
        $cupGroup = CupGroupFactory::fromId($cupGroupId);
        $cupPoints = $service->calculateCup($cup, $cupEvents, $cupGroup);
        /** @var Collection $persons */
        $persons = Person::where('active', true)->whereIn('id', array_keys($cupPoints))->get()->keyBy('id');

        dump(count($cupPoints));
        dd(array_diff(array_keys($cupPoints), $persons->keys()->all()));

        /** @see /resources/views/cup/table.blade.php */
        return $this->view('cup.table', [
            'cup' => $cup,
            'cupEvents' => $cupEvents,
            'cupPoints' => $cupPoints,
            'persons' => $persons,
            'activeGroup' => $cupGroup,
        ]);
    }

    protected function isNavbarRoute(): bool
    {
        return true;
    }
}
