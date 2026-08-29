<?php

declare(strict_types=1);

namespace App\Application\Dto\Serialization;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
final readonly class Groups
{
    /** @param list<string> $groups */
    public function __construct(public array $groups)
    {
    }
}
