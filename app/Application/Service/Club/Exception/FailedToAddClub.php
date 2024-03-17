<?php

declare(strict_types=1);

namespace App\Application\Service\Club\Exception;

use DomainException;
use RuntimeException;
use function sprintf;

final class FailedToAddClub extends RuntimeException
{
    public static function dueError(DomainException $e): self
    {
        return new self(sprintf('Unable to add club. Reason: %s', $e->getMessage()), previous: $e);
    }
}
