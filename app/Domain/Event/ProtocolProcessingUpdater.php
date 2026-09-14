<?php

declare(strict_types=1);

namespace App\Domain\Event;

use App\Domain\Auth\Impression;
use App\Domain\Event\Factory\EventProtocolFactory;

final readonly class ProtocolProcessingUpdater implements ProtocolUpdater
{
    public function __construct(
        private ProtocolUpdater $decorated,
        private EventProtocolFactory $factory,
        private EventProtocolRepository $protocols,
    ) {
    }

    public function update(Event $event, Protocol $protocol, Impression $impression): string
    {
        $path = $this->decorated->update($event, $protocol, $impression);
        $run = $this->factory->create($event->id, $impression);
        $this->protocols->add($run);
        $event->activateProtocolRun($run->id, $impression);

        return $path;
    }
}
