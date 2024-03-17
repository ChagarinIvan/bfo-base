<?php

declare(strict_types=1);

namespace App\Domain\Club\Exception;

use DomainException;
use function sprintf;

final class ClubAlreadyExist extends DomainException
{
    public static function byName(string $name): self
    {
        return new self(sprintf('Club with name "%s" already exist.', $name));
    }
}
