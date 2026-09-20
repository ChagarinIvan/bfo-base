<?php

declare(strict_types=1);

namespace App\Application\Service\Event;

use App\Application\Dto\Event\SearchEventDto;
use App\Domain\Event\EventResources;
use App\Domain\Shared\Criteria;
use function array_filter;
use function get_object_vars;
use function in_array;

final readonly class ListEvents
{
    public function __construct(private SearchEventDto $search)
    {
    }

    public function criteria(): Criteria
    {
        return new Criteria(array_filter(
            get_object_vars($this->search),
            static fn (mixed $value, string $key): bool => !in_array($key, ['withCompetition', 'withParticipantsCount'], true) && $value !== null,
            ARRAY_FILTER_USE_BOTH,
        ));
    }

    public function resources(): EventResources
    {
        return new EventResources(
            withCompetitionName: $this->search->withCompetition === '1',
            withParticipantsCount: $this->search->withParticipantsCount === '1',
        );
    }
}
