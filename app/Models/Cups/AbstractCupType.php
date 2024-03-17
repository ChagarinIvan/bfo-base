<?php

declare(strict_types=1);

namespace App\Models\Cups;

use App\Domain\ProtocolLine\ProtocolLine;
use App\Models\Cup;
use App\Models\CupEvent;
use App\Models\CupEventPoint;
use App\Models\Group\CupGroup;
use App\Repositories\ProtocolLinesRepository;
use App\Services\DistanceService;
use App\Services\GroupsService;
use Illuminate\Support\Collection;
use function array_slice;
use function round;
use function uasort;

abstract class AbstractCupType implements CupTypeInterface
{
    abstract protected function getGroupProtocolLines(CupEvent $cupEvent, CupGroup $group): Collection;
    public function __construct(
        protected readonly DistanceService $distanceService,
        protected readonly ProtocolLinesRepository $protocolLinesRepository,
        protected readonly GroupsService $groupsService,
    ) {
    }

    public function calculateCup(Cup $cup, Collection $cupEvents, CupGroup $mainGroup): array
    {
        $results = Collection::make();
        foreach ($cupEvents as $cupEvent) {
            $results = $results->merge($this->calculateEvent($cupEvent, $mainGroup));
        }

        $results = $results->groupBy('protocolLine.person_id');
        $results = $results->toArray();
        foreach ($results as &$cupEventPoints) {
            uasort($cupEventPoints, static function (CupEventPoint $a, CupEventPoint $b) {
                if ($a->points === $b->points) {
                    return 0;
                }
                return ($a->points !== '-' ? $a->points : 0) > ($b->points !== '-' ? $b->points : 0) ? -1 : 1;
            });
        }

        uasort($results, static function (array $person1Results, array $person2Results) use ($cup) {
            $person1Points = 0;
            foreach (array_slice($person1Results, 0, $cup->events_count) as $cupEventPoints) {
                /** @var CupEventPoint $cupEventPoints */
                $person1Points += $cupEventPoints->points;
            }
            $person2Points = 0;
            foreach (array_slice($person2Results, 0, $cup->events_count) as $cupEventPoints) {
                /** @var CupEventPoint $cupEventPoints */
                $person2Points += $cupEventPoints->points;
            }
            if ($person1Points === $person2Points) {
                return 0;
            }
            return $person1Points > $person2Points ? -1 : 1;
        });

        return $results;
    }

    public function getCupEventParticipatesCount(CupEvent $cupEvent): int
    {
        $groups = $cupEvent->cup->getCupType()->getGroups();
        $lines = Collection::empty();

        foreach ($groups as $group) {
            $lines = $lines->merge($this->getGroupProtocolLines($cupEvent, $group));
        }

        return $lines->pluck('id')->unique()->count();
    }

    protected function calculateLines(CupEvent $cupEvent, Collection $protocolLines): Collection
    {
        $cupEventPointsList = Collection::make();
        $maxPoints = $cupEvent->points;

        $protocolLines = $protocolLines->sortByDesc(static fn (ProtocolLine $line) => $line->time ? $line->time->diffInSeconds() : 0);

        $first = true;
        //а этапах Кубков Федерации очки начисляются по формуле:
        //O = Kus × (2W ÷ T − 1),
        //где T – результат спортсмена в секундах, W – результат победителя в секундах, Kus – коэффициент уровня соревнований.

        foreach ($protocolLines as $protocolLine) {
            /** @var ProtocolLine $protocolLine */
            if ($first) {
                if ($protocolLine->person_id !== null) {
                    /** @var ProtocolLine $firstResult */
                    $firstResult = $protocolLines->first();
                    $firstResultSeconds = $firstResult->time ? $firstResult->time->secondsSinceMidnight() : 0;
                    $cupEventPoints = new CupEventPoint(
                        $cupEvent->id,
                        $protocolLine,
                        $firstResult->time === null ? 0 : $maxPoints
                    );
                    $first = false;
                } else {
                    $cupEventPoints = new CupEventPoint($cupEvent->id, $protocolLine, '-');
                }
            } else {
                if ($protocolLine->person_id === null) {
                    $points = '-';
                } elseif ($protocolLine->time !== null) {
                    $diff = $protocolLine->time->secondsSinceMidnight();
                    $points = (int)round($maxPoints * (2 * ($firstResultSeconds ?? 0) / $diff - 1));
                    $points = $points < 0 ? 0 : $points;
                } else {
                    $points = 0;
                }
                $cupEventPoints = new CupEventPoint($cupEvent->id, $protocolLine, $points);
            }

            $cupEventPointsList->put($cupEventPoints->protocolLine->person_id, $cupEventPoints);
        }

        return $cupEventPointsList;
    }
}
