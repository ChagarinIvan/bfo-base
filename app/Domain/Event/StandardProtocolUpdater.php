<?php

declare(strict_types=1);

namespace App\Domain\Event;

use App\Domain\Shared\Storage;

final readonly class StandardProtocolUpdater implements ProtocolUpdater
{
    public function __construct(
        private Storage $storage,
        private ProtocolPathResolver $path,
    ) {
    }

    public function update(Event $event, Protocol $protocol): string
    {
        $this->storage->delete($event->file);
        $path = $this->path->protocolPath($event->date, $event->name, $protocol->extension);
        $this->storage->put($path, $protocol->content);

        return $path;
    }
}
