<?php

declare(strict_types=1);

namespace App\Domain\Event\Protocol;

interface ProtocolUrlReader
{
    public function read(string $url): string;
}
