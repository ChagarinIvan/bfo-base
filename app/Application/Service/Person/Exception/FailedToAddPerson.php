<?php

declare(strict_types=1);

namespace App\Application\Service\Person\Exception;

use DomainException;
use RuntimeException;
use function sprintf;

final class FailedToAddPerson extends RuntimeException
{
    public static function dueError(DomainException $e): self
    {
        return new self(sprintf('Unable to add person. Reason: %s', $e->getMessage()), previous: $e);
    }
}
