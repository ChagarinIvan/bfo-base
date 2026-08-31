<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Cup;

use App\Application\Dto\Club\LegacySearchClubDto;
use App\Application\Dto\Club\ViewClubDto;
use App\Application\Dto\Person\LegacySearchPersonDto;
use App\Application\Dto\Person\LegacyViewPersonDto;
use App\Application\Service\Club\ListLegacyClubs;
use App\Application\Service\Club\ListLegacyClubsService;
use App\Application\Service\Person\ListLegacyPersons;
use App\Application\Service\Person\ListLegacyPersonsService;
use App\Domain\Cup\Cup;
use App\Domain\Cup\Group\CupGroupFactory;
use App\Services\CupEventsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;
use function array_column;
use function array_filter;
use function array_keys;
use function array_map;
use function array_values;
use function preg_match;

class ShowCupTableAction extends BaseController
{
    use CupAction;

    public function __invoke(
        Cup $cup,
        string $cupGroupId,
        CupEventsService $service,
        ListLegacyPersonsService $listPersonsService,
        ListLegacyClubsService $listClubsService,
    ): RedirectResponse|View {
        // fix wrong group
        if (preg_match('#^(\D)_(\d+)$#', $cupGroupId)) {
            $cupGroupId .= '_';
        }

        $cupEvents = $service->getCupEvents((string) $cup->id)->sortBy('event.date');
        $cupGroup = CupGroupFactory::fromId($cupGroupId);
        $cupPoints = $service->calculateCup($cup, $cupEvents, $cupGroup);

        /** @var array<int, LegacyViewPersonDto> $persons */
        $persons = array_column(
            array: $listPersonsService->execute(new ListLegacyPersons(new LegacySearchPersonDto(ids: array_keys($cupPoints)))),
            column_key: null,
            index_key: 'id',
        );

        $clubIds = array_values(array_filter(array_map(
            static fn (LegacyViewPersonDto $person): ?string => $person->clubId,
            $persons,
        )));

        /** @var array<int, ViewClubDto> $clubs */
        $clubs = array_column(
            array: $listClubsService->execute(new ListLegacyClubs(new LegacySearchClubDto(ids: $clubIds))),
            column_key: null,
            index_key: 'id',
        );

        /** @see /resources/views/cup/table.blade.php */
        return $this->view('cup.table', [
            'cup' => $cup,
            'cupEvents' => $cupEvents,
            'cupPoints' => $cupPoints,
            'persons' => $persons,
            'clubs' => $clubs,
            'activeGroup' => $cupGroup,
        ]);
    }

    protected function isNavbarRoute(): bool
    {
        return true;
    }
}
