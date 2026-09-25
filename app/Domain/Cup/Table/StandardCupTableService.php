<?php

declare(strict_types=1);

namespace App\Domain\Cup\Table;

use App\Domain\Cup\Cup;
use App\Domain\Cup\CupEvent\CupEventPoint;
use App\Domain\Cup\Group\CupGroup;
use Illuminate\Support\Collection;
use function count;
use function is_numeric;
use function round;

final readonly class StandardCupTableService implements CupTableService
{
    public function build(Cup $cup, Collection $events, CupGroup $group): CupTable
    {
        $calculated = $cup->calculateGroupEvents($group, $events);
        $rows = [];
        $countedEvents = $cup->events_count;
        $place = 1;

        foreach ($calculated as $points) {
            /** @var CupEventPoint|null $first */
            $first = $points[0] ?? null;
            if ($first === null) {
                continue;
            }

            $firstLine = $first->protocolLine;
            if ($firstLine->person_id === null) {
                continue;
            }

            $personId = (int) $firstLine->person_id;
            $cells = [];
            $total = 0.0;
            $countedPoints = 0;

            foreach ($points as $index => $point) {
                $line = $point->protocolLine;
                $counted = $index < $countedEvents;
                $cells[(string) $point->cupEventId] = new CupTableStageCell(
                    stageId: $point->cupEventId,
                    points: (string) $point->points,
                    counted: $counted,
                    distanceId: (string) $line->distance_id,
                    protocolLineId: (string) $line->id,
                );

                if ($counted && is_numeric($point->points)) {
                    $total += (float) $point->points;
                    if ((float) $point->points !== 0.0) {
                        ++$countedPoints;
                    }
                }
            }

            /** @var array<string, CupTableStageCell> $cells */
            $rows[] = new CupTableRow(
                place: $place++,
                personId: (string) $personId,
                personName: $firstLine->getFullName(),
                personYear: $firstLine->year ?? 0,
                clubName: $firstLine->club,
                stages: $cells,
                totalPoints: (string) $total,
                averagePoints: (string) ($countedPoints === 0 ? 0 : round($total / $countedPoints)),
            );
        }

        $stages = $events->map(static fn ($event): CupTableStage => new CupTableStage(
            stageId: (int) $event->id,
            eventId: (string) $event->event_id,
            date: $event->event->date->format('Y-m-d'),
            name: $event->event->name,
        ))->values()->all();

        return new CupTable($stages, $rows);
    }
}
