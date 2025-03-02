<?php

declare(strict_types=1);

namespace App\Domain\Shared;

use OutOfRangeException;
use function array_flip;
use function array_intersect_key;
use function count;

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

    /** @param string[] $param */
    public function hasOneParam(array $param): bool
    {
        return count(array_intersect_key($this->params, array_flip($param))) > 0;
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

    public function paramOrDefault(string $key, mixed $default = null): mixed
    {
        return $this->params[$key] ?? $default;
    }
}
