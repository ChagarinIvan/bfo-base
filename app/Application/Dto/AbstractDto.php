<?php

declare(strict_types=1);

namespace App\Application\Dto;

use function array_key_exists;

abstract class AbstractDto
{
    /** @return array<string, mixed> */
    public static function requestValidationRules(): array
    {
        return [];
    }

    /** @return array<string, mixed> */
    public static function parametersValidationRules(): array
    {
        return [];
    }

    /** @param array<string, mixed> $data */
    abstract public function fromArray(array $data): self;

    public function fromRequest(): bool
    {
        return true;
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function setStringParam(string $name, array $data): void
    {
        if (array_key_exists($name, $data)) {
            $this->$name = $data[$name];
        }
    }
}
