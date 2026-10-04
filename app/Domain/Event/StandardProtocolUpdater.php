<?php

declare(strict_types=1);

namespace App\Domain\Event;

use App\Domain\Auth\Impression;
use App\Domain\Shared\Storage;

final readonly class StandardProtocolUpdater implements ProtocolUpdater
{
    public function __construct(
        private Storage $storage,
        private ProtocolPathResolver $path,
    ) {
    }

    public function update(Event $event, Protocol $protocol, Impression $impression): string
    {
        $path = $this->path->protocolPath($event->date, $event->name, $protocol->extension);
        $this->storage->put($path, $protocol->content);

        if ($path !== $event->file && $event->file !== '') {
            $this->storage->delete($event->file);
        }

        return $path;
    }
}
