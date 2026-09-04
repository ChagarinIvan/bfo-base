<?php

declare(strict_types=1);

namespace App\Domain\Group;

final readonly class GroupInfo
{
    public function __construct(public string $name, public string $normalizeName)
    {
    }
}
