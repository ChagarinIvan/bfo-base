<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Event;

use App\Application\Dto\Club\ClubSearchDto;
use App\Application\Dto\Event\ViewEventDto;
use App\Application\Dto\Person\PersonSearchDto;
use App\Application\Service\Club\ListClubs;
use App\Application\Service\Club\ListClubsService;
use App\Application\Service\Person\ListPersons;
use App\Application\Service\Person\ListPersonsService;
use App\Domain\Club\NormalizedNameClubFinder;
use App\Domain\Distance\Distance;
use Illuminate\Contracts\View\View;
use function array_column;

/**
 * Shared rendering of a single distance protocol. Every dependency is fetched
 * up-front (one query each) so the view stays free of N+1 lookups.
 *
 * @method View view(string $template, array $data = [])
 */
trait RendersEventDistance
{
    protected function renderEventDistance(
        ViewEventDto $event,
        Distance $distance,
        ListClubsService $clubsService,
        ListPersonsService $personsService,
    ): View {
        $protocolLines = $distance->protocolLines;

        $withPoints = false;
        $withVk = false;
        foreach ($protocolLines as $protocolLine) {
            $withPoints = $withPoints || $protocolLine->points !== null;
            $withVk = $withVk || $protocolLine->vk;
            if ($withPoints && $withVk) {
                break;
            }
        }

        $personIds = $protocolLines->pluck('person_id')->filter()->unique()->values()->all();
        $persons = array_column(
            array: $personsService->execute(new ListPersons(new PersonSearchDto(ids: $personIds))),
            column_key: null,
            index_key: 'id',
        );

        $clubs = array_column(
            array: $clubsService->execute(new ListClubs(new ClubSearchDto())),
            column_key: null,
            index_key: 'normalizeName',
        );

        $clubsByLine = [];
        foreach ($protocolLines as $protocolLine) {
            $normalized = NormalizedNameClubFinder::normalizeName($protocolLine->club);
            if (isset($clubs[$normalized])) {
                $clubsByLine[$protocolLine->id] = $clubs[$normalized];
            }
        }

        /** @see /resources/views/events/show.blade.php */
        return $this->view('events.show', [
            'event' => $event,
            'lines' => $protocolLines,
            'withPoints' => $withPoints,
            'withVk' => $withVk,
            'selectedDistance' => $distance,
            'clubsByLine' => $clubsByLine,
            'persons' => $persons,
        ]);
    }
}
