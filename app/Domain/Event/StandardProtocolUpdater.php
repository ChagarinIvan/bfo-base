<?php

declare(strict_types=1);

namespace App\Domain\Event;

final readonly class StandardProtocolUpdater implements ProtocolUpdater
{
    public function __construct(
        private ProtocolStorage $storage,
        private ProtocolPathResolver $path,
    ) {
    }

    public function update(Event $event, Protocol $protocol): string
    {
        $this->storage->delete($event->file);
        dd($protocol->extension);
        $path = $this->path->protocolPath($event->date, $event->name, $protocol->extension);
        $this->storage->put($path, $protocol);

        return $path;
    }
}
