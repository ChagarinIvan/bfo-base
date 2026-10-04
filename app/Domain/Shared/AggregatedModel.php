<?php

declare(strict_types=1);

namespace App\Domain\Shared;

use Illuminate\Database\Eloquent\Model;
use function array_map;

abstract class AggregatedModel extends Model
{
    /** @var AggregatedEvent[] */
    private array $modelEvents = [];

    public function save(array $options = []): bool
    {
        $result = parent::save($options);

        if ($result) {
            $this->releaseEvents();
        }

        return $result;
    }

    /** @return AggregatedEvent[] */
    public function releasedEvents(): array
    {
        return $this->modelEvents;
    }

    protected function recordThat(AggregatedEvent $event): void
    {
        $this->modelEvents[] = $event;
    }

    private function releaseEvents(): void
    {
        $events = $this->modelEvents;
        $this->modelEvents = [];

        array_map(event(...), $events);
    }
}
