<?php

declare(strict_types=1);

namespace App\Infrastructure\Laravel\Eloquent\Rank;

use App\Domain\Person\RankFact;
use App\Domain\Person\RankFactsCollector;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\Rank\Rank;
use App\Domain\Rank\RankNormalizer;
use Carbon\Carbon;

final readonly class EloquentRankFactsCollector implements RankFactsCollector
{
    public function __construct(private RankNormalizer $normalizer)
    {
    }

    public function collect(int $personId): array
    {
        return ProtocolLine::query()
            ->with(['distance.event.competition'])
            ->where('person_id', $personId)
            ->get()
            ->map(function (ProtocolLine $line): ?RankFact {
                $rank = $this->normalizer->normalize($line->complete_rank);

                if ($rank === null
                    || $rank === Rank::WithoutRank
                    || $line->person_id === null
                    || $line->distance->event->competition->mass
                    || $line->time === null
                    || $line->vk) {
                    return null;
                }

                return new RankFact(
                    protocolLineId: $line->id,
                    distanceId: $line->distance_id,
                    eventId: $line->distance->event_id,
                    competitionId: $line->distance->event->competition_id,
                    rank: $rank,
                    achievedOn: Carbon::instance($line->distance->event->date),
                    activatedOn: $line->activate_rank === null ? null : Carbon::instance($line->activate_rank),
                );
            })
            ->filter()
            ->values()
            ->all()
        ;
    }
}
