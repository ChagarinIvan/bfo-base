<?php

declare(strict_types=1);

namespace App\Domain\Shared;

use Illuminate\Database\Eloquent\Model;
use function array_map;

abstract class AggregatedModel extends Model
{
    /** @var AggregatedEvent[] */
    private array $events = [];

    public function save(array $options = []): bool
    {
        $result = parent::save($options);
        $this->releaseEvents();

        return $result;
    }

    /** @return AggregatedEvent[] */
    public function releasedEvents(): array
    {
        return $this->events;
    }

    protected function recordThat(AggregatedEvent $event): void
    {
        $this->events[] = $event;
    }

    private function releaseEvents(): void
    {
        array_map(event(...), $this->events);
    }
}
