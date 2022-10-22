<?php

namespace App\Domain\Club;

final class Club
{
    public function __construct(
        private readonly string $id,
        private readonly string $name,
        private readonly string $normalizeName,
    ) {}

    public function id(): string
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }
}
