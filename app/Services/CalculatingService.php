<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Cup;
use App\Models\CupEvent;
use App\Models\CupEventPoint;
use App\Models\ProtocolLine;
use Illuminate\Support\Collection;

/**
 * Class CalculatingService
 *
 * @package App\Services
 */
class CalculatingService
{
    /**
     * @param Cup $cup
     * @param Collection|ProtocolLine[] $protocolLines
     * @return array
     */
    public static function calculateCup(Cup $cup, Collection $protocolLines): array
    {
        $groupedProtocolLines = $protocolLines->groupBy('event_id');
        $protocolLines = $protocolLines->keyBy('id');

        $results = [];
        foreach ($cup->events as $event) {
            $eventResults = self::calculateEvent($event, $groupedProtocolLines->get($event->event_id));
            foreach ($eventResults as $result) {
                /** @var ProtocolLine $protocolLine */
                $protocolLine = $protocolLines->get($result->protocolLineId);
                $results[$protocolLine->person_id][$event->id] = $result;
            }
        }

        foreach ($results as &$cupEventPoints) {
            uasort($cupEventPoints, function (CupEventPoint $a, CupEventPoint $b) {
                if ($a->points === $b->points) {
                    return 0;
                }
                return ($a->points !== '-' ? $a->points : 0) > ($b->points !== '-' ? $b->points : 0) ? -1 : 1;
            });
        }

        uasort($results, function (array $person1Results, array $person2Results) use ($cup) {
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

    /**
     * @param CupEvent $cupEvent
     * @param Collection $protocolLines
     * @return array<int, CupEventPoint>
     */
    public static function calculateEvent(CupEvent $cupEvent, Collection $protocolLines): array
    {
        if ($protocolLines->isEmpty()) {
            return [];
        }

        $maxPoints = $cupEvent->points;
        $cupEventPointsList = [];

        $protocolLines = $protocolLines->sortByDesc(function (ProtocolLine $line) {
            return $line->time ? $line->time->diffInSeconds() : 0;
        });

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
                    $cupEventPoints = new CupEventPoint($cupEvent->id, $protocolLine->id, $maxPoints);
                    $first = false;
                } else {
                    $cupEventPoints = new CupEventPoint($cupEvent->id, $protocolLine->id, '-');
                }
            } else {
                if ($protocolLine->person_id === null) {
                    $points = '-';
                } elseif ($protocolLine->time !== null) {
                    $diff = $protocolLine->time->secondsSinceMidnight();
                    $points = (int)round($maxPoints * (2 * $firstResultSeconds / $diff - 1));
                    $points = $points < 0 ? 0 : $points;
                } else {
                    $points = 0;
                }
                $cupEventPoints = new CupEventPoint($cupEvent->id, $protocolLine->id, $points);
            }
            $cupEventPointsList[$cupEventPoints->protocolLineId] = $cupEventPoints;
        }

        return $cupEventPointsList;
    }
}
