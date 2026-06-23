<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Cup;

use App\Application\Dto\Club\ClubSearchDto;
use App\Application\Dto\Person\PersonSearchDto;
use App\Application\Dto\Person\ViewPersonDto;
use App\Application\Service\Club\ListClubs;
use App\Application\Service\Club\ListClubsService;
use App\Application\Service\Person\ListPersons;
use App\Application\Service\Person\ListPersonsService;
use App\Domain\Cup\Cup;
use App\Domain\Cup\Group\CupGroupFactory;
use App\Domain\Person\Person;
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
        ListPersonsService $listPersonsService,
        ListClubsService $listClubsService,
    ): View|RedirectResponse {
        // fix wrong group
        if (preg_match('#^(\D)_(\d+)$#', $cupGroupId)) {
            $cupGroupId .= '_';
        }

        $cupEvents = $service->getCupEvents((string) $cup->id)->sortBy('event.date');
        $cupGroup = CupGroupFactory::fromId($cupGroupId);
        $cupPoints = $service->calculateCup($cup, $cupEvents, $cupGroup);

        /** @var array<int, ViewPersonDto> $persons */
        $persons = array_column(
            array: $listPersonsService->execute(new ListPersons(new PersonSearchDto(ids: array_keys($cupPoints)))),
            column_key: null,
            index_key: 'id',
        );

        $clubIds = array_values(array_filter(array_map(
            static fn (ViewPersonDto $person): ?string => $person->clubId,
            $persons,
        )));

        /** @var array<int, \App\Application\Dto\Club\ViewClubDto> $clubs */
        $clubs = array_column(
            array: $listClubsService->execute(new ListClubs(new ClubSearchDto(ids: $clubIds))),
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
