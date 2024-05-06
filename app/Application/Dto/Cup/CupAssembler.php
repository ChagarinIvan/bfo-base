<?php

declare(strict_types=1);

namespace App\Application\Dto\Cup;

use App\Application\Dto\Auth\AuthAssembler;
use App\Application\Dto\CupEvent\CupEventAssembler;
use App\Domain\Cup\Cup;
use App\Domain\Cup\CupType\CupTypeInterface;
use App\Domain\Cup\Group\CupGroup;
use App\Domain\CupEvent\CupEvent;
use App\Domain\Event\EventRepository;
use App\Domain\Shared\Criteria;
use function array_map;

final readonly class CupAssembler
{
    public function __construct(
        private EventRepository $events,
        private AuthAssembler $authAssembler,
    ) {
    }

    public function toViewCupDto(Cup $cup): ViewCupDto
    {
        $eventCriteria = new Criteria(['cupId' => $cup->id], ['date' => 'desc']);
        $cupTypeInstance = $cup->type->instance();
        $cupGroups = $cupTypeInstance->getGroups()->toArray();

        return new ViewCupDto(
            id: (string) $cup->id,
            name: $cup->name,
            eventsCount: (string) $cup->events_count,
            year: $cup->year->value,
            type: $cup->type->value,
            groups: array_map($this->toViewCupGroupDto(...), $cupGroups),
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
}
