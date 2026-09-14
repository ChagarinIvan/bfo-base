<?php

declare(strict_types=1);

namespace App\Infrastructure\Laravel\Eloquent\Event;

use App\Domain\Event\EventProtocol;
use App\Domain\Event\EventProtocolRepository;

final readonly class EloquentEventProtocolRepository implements EventProtocolRepository
{
    public function add(EventProtocol $protocol): void
    {
        $protocol->create();
    }

    public function byId(int $id): ?EventProtocol
    {
        return EventProtocol::query()->find($id);
    }

    public function update(EventProtocol $protocol): void
    {
        $protocol->save();
    }
}
