<?php

declare(strict_types=1);

namespace App\Application\Dto\Rank;

use App\Application\Dto\Auth\AuthAssembler;
use App\Application\Dto\Cup\CupEvent\ViewCupEventDto;
use App\Application\Dto\Cup\CupEvent\ViewCupEventPointDto;
use App\Application\Dto\Cup\ViewCalculatedCupEventDto;
use App\Application\Dto\Cup\ViewCupDto;
use App\Application\Dto\Cup\ViewCupGroupDto;
use App\Application\Dto\Event\EventAssembler;
use App\Domain\Cup\Cup;
use App\Domain\Cup\CupEvent\CupEvent;
use App\Domain\Cup\CupEvent\CupEventPoint;
use App\Domain\Cup\CupRepository;
use App\Domain\Cup\Group\CupGroup;
use App\Domain\Event\EventRepository;
use App\Domain\ProtocolLine\ProtocolLineRepository;
use App\Domain\Rank\Rank;
use App\Domain\Shared\Criteria;
use function array_map;
use function sprintf;

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
}
