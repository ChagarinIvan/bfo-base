<?php

declare(strict_types=1);

namespace App\Domain\Event\Factory;

use App\Domain\Auth\Impression;
use App\Domain\Event\Event;
use App\Domain\Event\EventInfo;
use Carbon\Carbon;
use Illuminate\Support\Collection;

final readonly class UniteFactory
{
    public function __construct(private EventFactory $events)
    {
    }

    /** @param Collection<int, Event> $events */
    public function create(Collection $events, int $competitionId, Impression $impression): Event
    {
        /** @var Event $firstEvent */
        $firstEvent = $events->first();
        $name = $events->pluck('name')->implode(' + ');

        return $this->events->create(new EventInput(
            new EventInfo(
                name: $name,
                description: "Аб'яднанне этапаў: {$name}",
                date: Carbon::instance($firstEvent->date),
            ),
            $competitionId,
            $impression->by,
        ));
    }
}
