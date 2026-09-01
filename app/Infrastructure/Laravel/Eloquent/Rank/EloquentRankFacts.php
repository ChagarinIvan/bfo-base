<?php

declare(strict_types=1);

namespace App\Infrastructure\Laravel\Eloquent\Rank;

use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\Rank\Rank;
use App\Domain\Rank\RankAchievement;
use App\Domain\Rank\RankFacts;
use Carbon\CarbonImmutable;

final readonly class EloquentRankFacts implements RankFacts
{
    public function forPerson(int $personId): array
    {
        return ProtocolLine::query()
            ->with(['person', 'distance.event.competition'])
            ->where('person_id', $personId)
            ->get()
            ->map(static function (ProtocolLine $line): ?RankAchievement {
                $rank = Rank::fromProtocolValue($line->complete_rank);
                if ($rank === null || $rank === Rank::WithoutRank || $line->person_id === null) {
                    return null;
                }

                return new RankAchievement(
                    personId: $line->person_id,
                    protocolLineId: $line->id,
                    distanceId: $line->distance_id,
                    eventId: $line->distance->event_id,
                    competitionId: $line->distance->event->competition_id,
                    rank: $rank,
                    achievedOn: CarbonImmutable::instance($line->event->date),
                    activatedOn: $line->activate_rank === null ? null : CarbonImmutable::instance($line->activate_rank),
                    massCompetition: $line->distance->event->competition->mass,
                    outOfCompetition: (bool) $line->vk,
                    birthday: $line->person->birthday === null ? null : CarbonImmutable::instance($line->person->birthday),
                    hasResult: $line->time !== null,
                );
            })
            ->filter()
            ->values()
            ->all();
    }
}
