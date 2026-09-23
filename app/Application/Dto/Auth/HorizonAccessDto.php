<?php

declare(strict_types=1);

namespace App\Application\Dto\Auth;

use JsonSerializable;

final readonly class HorizonAccessDto implements JsonSerializable
{
    public function __construct(public bool $allowed)
    {
    }

    public function jsonSerialize(): array
    {
        return ['allowed' => $this->allowed];
    }
}
