<?php

declare(strict_types=1);

namespace App\Application\Service\Person\Exception;

use App\Domain\Person\Exception\PersonInfoAlreadyExist;
use RuntimeException;
use function sprintf;

final class FailedToAddPerson extends RuntimeException
{
    public static function personAlreadyExist(PersonInfoAlreadyExist $e): self
    {
        return new self(
            sprintf('Unable to add person. Reason: %s', $e->getMessage()),
            $e->previousPersonId,
        );
    }
    private function __construct(
        string $message,
        public readonly int $previousPersonId
    ) {
        parent::__construct($message);
    }
}
