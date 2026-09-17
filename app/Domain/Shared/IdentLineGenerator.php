<?php

declare(strict_types=1);

namespace App\Domain\Shared;

interface IdentLineGenerator
{
    public function generate(string $lastname, string $firstname, ?int $year = null): string;
}
