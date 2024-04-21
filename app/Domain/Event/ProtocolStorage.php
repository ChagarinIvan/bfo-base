<?php

declare(strict_types=1);

namespace App\Domain\Event;

interface ProtocolStorage
{
    public function put(string $path, Protocol $protocol): void;

    public function get(string $path): Protocol;

    public function delete(string $path): void;
}
