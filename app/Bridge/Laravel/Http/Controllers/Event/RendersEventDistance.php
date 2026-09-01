<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Event;

use App\Application\Dto\Club\LegacySearchClubDto;
use App\Application\Dto\Event\LegacyViewEventDto;
use App\Application\Dto\Person\LegacySearchPersonDto;
use App\Application\Service\Club\ListLegacyClubs;
use App\Application\Service\Club\ListLegacyClubsService;
use App\Application\Service\Person\ListLegacyPersons;
use App\Application\Service\Person\ListLegacyPersonsService;
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
        LegacyViewEventDto $event,
        Distance           $distance,
        ListLegacyClubsService   $clubsService,
        ListLegacyPersonsService $personsService,
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
            array: $personsService->execute(new ListLegacyPersons(new LegacySearchPersonDto(ids: $personIds))),
            column_key: null,
            index_key: 'id',
        );

        $clubs = [];
        foreach ($clubsService->execute(new ListLegacyClubs(new LegacySearchClubDto())) as $club) {
            $clubs[NormalizedNameClubFinder::normalizeName($club->name)] = $club;
        }

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
