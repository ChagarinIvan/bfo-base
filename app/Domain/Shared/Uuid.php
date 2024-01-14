<?php

declare(strict_types=1);

namespace App\Domain\Shared;

use Ramsey\Uuid\Uuid as BaseUuid;
use Ramsey\Uuid\UuidInterface;

readonly class Uuid
{
    public static function fromString(string $value): static
    {
        return new static(BaseUuid::fromString($value));
    }

    public static function random(): static
    {
        return new static(BaseUuid::uuid4());
    }

    final public function __construct(private UuidInterface $value)
    {
    }

    public function toString(): string
    {
        return $this->value->toString();
    }
}
