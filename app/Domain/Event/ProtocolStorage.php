<?php

declare(strict_types=1);

namespace App\Domain\Event;

interface ProtocolStorage
{
    public function put(string $path, string $content): void;

    public function get(string $path): string;
}
