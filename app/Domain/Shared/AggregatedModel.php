<?php

declare(strict_types=1);

namespace App\Domain\Shared;

use Illuminate\Database\Eloquent\Model;

abstract class AggregatedModel extends Model
{
    /** @var AggregatedEvent[] */
    private array $events = [];

    private function releaseEvents(): void
    {
        array_map(event(...), $this->events);
    }

    protected function recordThat(AggregatedEvent $event): void
    {
        $this->events[] = $event;
    }

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
}
