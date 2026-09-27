<?php

declare(strict_types=1);

namespace App\Domain\Shared;

interface Storage
{
    public function put(string $path, string $content): void;

    public function get(string $path): string;

    public function delete(string $path): void;
}
