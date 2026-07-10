<?php

declare(strict_types=1);

namespace App\Application\Dto\Rank;

use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\ProtocolLine\ProtocolLineRepository;
use App\Domain\Rank\Rank;
use App\Domain\Shared\Criteria;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use function array_map;
use function array_values;

final readonly class RankAssembler
{
    public function __construct(private ProtocolLineRepository $protocolLines)
    {
    }

    public function toViewRankDto(Rank $rank): ViewRankDto
    {
        $protocolLine = $rank->event_id
            ? $this->protocolLines->oneByCriteria(new Criteria(['personId' => $rank->person_id, 'eventId' => $rank->event_id]))
            : null
        ;

        return $this->assemble($rank, $protocolLine);
    }

    /**
     * Batch variant that avoids the per-rank N+1: eager-loads the rank relations and
     * resolves every protocol line in a single query per person.
     *
     * @param Rank[] $ranks
     * @return ViewRankDto[]
     */
    public function toViewRankDtos(array $ranks): array
    {
        EloquentCollection::make($ranks)->loadMissing(['person', 'event.competition']);

        $protocolLines = $this->preloadProtocolLines($ranks);

        return array_map(
            fn (Rank $rank): ViewRankDto => $this->assemble(
                $rank,
                $protocolLines[$rank->person_id][$rank->event_id] ?? null,
            ),
            $ranks,
        );
    }

    private function assemble(Rank $rank, ?ProtocolLine $protocolLine): ViewRankDto
    {
        return new ViewRankDto(
            id: (string) $rank->id,
            rank: $rank->rank,
            eventId: (string) $rank->event_id,
            startDate: $rank->start_date->format('Y-m-d'),
            finishDate: $rank->finish_date->format('Y-m-d'),
            activatedDate: $rank->activated_date?->format('Y-m-d'),
            personId: (string) $rank->person_id,
            personFirstname: $rank->person->firstname,
            personLastname: $rank->person->lastname,
            distanceId: $protocolLine ? (string) $protocolLine->distance_id : null,
            protocolLineId: $protocolLine ? (string) $protocolLine->id : null,
            competitionName: $rank->event?->competition?->name,
            eventName: $rank->event?->name,
            eventDate: $rank->event?->date->format('Y-m-d'),
        );
    }

    /**
     * @param Rank[] $ranks
     * @return array<int, array<int, ProtocolLine>> map of [personId][eventId] => ProtocolLine
     */
    private function preloadProtocolLines(array $ranks): array
    {
        $personIds = [];
        $eventIds = [];
        $pairs = [];
        foreach ($ranks as $rank) {
            if ($rank->event_id) {
                $personIds[$rank->person_id] = $rank->person_id;
                $eventIds[$rank->event_id] = $rank->event_id;
                $pairs["{$rank->person_id}:{$rank->event_id}"] = true;
            }
        }

        if ($pairs === []) {
            return [];
        }

        $lines = $this->protocolLines->byCriteria(new Criteria([
            'personIds' => array_values($personIds),
            'eventIds' => array_values($eventIds),
        ]));

        $map = [];
        /** @var ProtocolLine $line */
        foreach ($lines as $line) {
            $eventId = (int) $line->getAttribute('event_id');
            if (isset($pairs["{$line->person_id}:{$eventId}"])) {
                $map[$line->person_id][$eventId] ??= $line;
            }
        }

        return $map;
    }
}
