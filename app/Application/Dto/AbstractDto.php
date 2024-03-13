<?php

declare(strict_types=1);

namespace App\Application\Dto;

use function array_key_exists;

abstract class AbstractDto
{
    /** @return array<string, mixed> */
    abstract public static function validationRules(): array;

    /** @param array<string, mixed> $data */
    abstract public function fromArray(array $data): self;

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
