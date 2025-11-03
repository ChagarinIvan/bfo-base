<?php

declare(strict_types=1);

namespace App\Application\Dto\Cup;

use App\Application\Dto\Auth\AuthAssembler;
use App\Application\Dto\Cup\CupEvent\ViewCupEventDto;
use App\Application\Dto\Cup\CupEvent\ViewCupEventPointDto;
use App\Application\Dto\Event\EventAssembler;
use App\Domain\Cup\Cup;
use App\Domain\Cup\CupEvent\CupEvent;
use App\Domain\Cup\CupEvent\CupEventPoint;
use App\Domain\Cup\CupRepository;
use App\Domain\Cup\Group\CupGroup;
use App\Domain\Event\EventRepository;
use App\Domain\Shared\Criteria;
use function array_map;
use function sprintf;

final readonly class CupAssembler
{
    public function __construct(
        private EventRepository $events,
        private EventAssembler $eventAssembler,
        private AuthAssembler $authAssembler,
    ) {
    }

    public function toViewCupDto(Cup $cup): ViewCupDto
    {
        $eventCriteria = new Criteria(['cupId' => $cup->id], ['date' => 'desc']);
        dump($cup);

        return new ViewCupDto(
            id: (string) $cup->id,
            name: $cup->name,
            eventsCount: (string) $cup->events_count,
            year: $cup->year->value,
            type: $cup->type->value,
            groups: $this->toViewCupGroupsDto($cup),
            cupEvents: $cup->events->map($this->toViewCupEventDto(...))->all(),
            lastEventDate: $this->events->oneByCriteria($eventCriteria)?->date->format('Y-m-d') ?? '',
            visible: $cup->visible,
            created: $this->authAssembler->toImpressionDto($cup->created),
            updated: $this->authAssembler->toImpressionDto($cup->updated),
        );
    }

    public function toViewCupGroupDto(CupGroup $group): ViewCupGroupDto
    {
        return new ViewCupGroupDto(
            id: $group->id(),
            name: $group->name(),
        );
    }

    /** @property CupEventPoint[] $points */
    public function toViewCalculatedCupEventDto(Cup $cup, CupEvent $cupEvent, array $points): ViewCalculatedCupEventDto
    {
        return new ViewCalculatedCupEventDto(
            cupName: $cup->name,
            cupYear: $cup->year->toString(),
            cupGroups: $this->toViewCupGroupsDto($cup),
            cupEvent: $this->toViewCupEventDto($cupEvent),
            points: array_map($this->toViewCupEventPointDto(...), $points),
        );
    }

    public function toViewCupEventDto(CupEvent $cupEvent): ViewCupEventDto
    {
        return new ViewCupEventDto(
            id: (string) $cupEvent->id,
            cupId: (string) $cupEvent->cup_id,
            eventId: (string) $cupEvent->event_id,
            points: (string) $cupEvent->points,
            created: $this->authAssembler->toImpressionDto($cupEvent->created),
            updated: $this->authAssembler->toImpressionDto($cupEvent->updated),

            // TODO remove
            event: $this->eventAssembler->toViewEventDto($cupEvent->event),
        );
    }

    private function toViewCupGroupsDto(Cup $cup): array
    {
        $cupTypeInstance = $cup->type->instance();
        $cupGroups = $cupTypeInstance->getGroups()->toArray();

        return array_map($this->toViewCupGroupDto(...), $cupGroups);
    }

    private function toViewCupEventPointDto(CupEventPoint $point): ViewCupEventPointDto
    {
        $protocolLine = $point->protocolLine;

        return new ViewCupEventPointDto(
            cupEventId: (string) $point->cupEventId,
            points: (string) $point->points,
            personId: (string) ($protocolLine->person_id ?? ''),
            personName: sprintf('%s %s', $protocolLine->lastname, $protocolLine->firstname),
            personYear: $protocolLine->year ?? 0,
            personClubId: $protocolLine->person?->club_id ? (string) $protocolLine->person->club_id : null,
            time: $protocolLine->time ? $protocolLine->time->format('H:i:s') : '-',
        );
    }
}
