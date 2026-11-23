<?php

declare(strict_types=1);

namespace App\Infrastructure\Laravel\Eloquent\Rank;

use App\Domain\Person\ManualRank\ManualPersonRank;
use App\Domain\Person\ManualRank\ManualPersonRankRepository;
use App\Domain\Person\RankFact;
use App\Domain\Person\RankFactsCollector;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\Rank\Rank;
use App\Domain\Rank\RankNormalizer;
use App\Domain\Shared\Criteria;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use function array_map;

final readonly class EloquentRankFactsCollector implements RankFactsCollector
{
    public function __construct(private RankNormalizer $normalizer, private ManualPersonRankRepository $manualRanks)
    {
    }

    public function collect(int $personId): array
    {
        $protocolFacts = ProtocolLine::query()
            ->with(['distance.event.competition'])
            ->where('person_id', $personId)
            ->whereHas('distance.event', static function (Builder $query): void {
                $query
                    ->where('events.active', true)
                    ->whereHas('competition', static function (Builder $query): void {
                        $query->where('competitions.active', true);
                    });
            })
            ->get()
            ->map(function (ProtocolLine $line): ?RankFact {
                $rank = $this->normalizer->normalize($line->complete_rank);

                if (!$rank instanceof Rank
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
                    delayedActivation: $line->delayed_rank_activation,
                );
            })
            ->filter()
            ->values()
            ->all()
        ;

        return [...$protocolFacts, ...array_map(
            static fn (ManualPersonRank $rank): RankFact => $rank->fact(),
            $this->manualRanks->byCriteria(new Criteria(['personId' => $personId])),
        )];
    }
}
