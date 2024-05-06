<?php

declare(strict_types=1);

namespace App\Domain\Shared;

use OutOfRangeException;

readonly class Criteria
{
    public static function empty(): self
    {
        return new self([]);
    }
    public function __construct(
        /** @var array<string, mixed> $params */
        private array $params,
        /** @var array<string, mixed> $params */
        private array $sorting = [],
    ) {
    }

    public function sorting(): array
    {
        return $this->sorting;
    }

    public function hasParam(string $param): bool
    {
        return isset($this->params[$param]);
    }

    /** @return array<string, mixed> */
    public function params(): array
    {
        return $this->params;
    }

    /** @throws OutOfRangeException */
    public function param(string $key): mixed
    {
        return $this->params[$key] ?? new OutOfRangeException('Has no param.');
    }
}
