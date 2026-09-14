<?php

declare(strict_types=1);

namespace App\Domain\Event;

interface EventProtocolRepository
{
    public function add(EventProtocol $protocol): void;

    public function byId(int $id): ?EventProtocol;

    public function update(EventProtocol $protocol): void;
}
