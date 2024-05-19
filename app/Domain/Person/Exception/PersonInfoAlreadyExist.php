<?php

declare(strict_types=1);

namespace App\Domain\Person\Exception;

use App\Domain\Person\PersonInfo;
use DomainException;
use function sprintf;

final class PersonInfoAlreadyExist extends DomainException
{
    public static function byInfo(PersonInfo $info, int $previousPersonId): self
    {
        return new self(sprintf(
            'Person "%s %s %s" already exist.',
            $info->lastname,
            $info->firstname,
            $info->birthday?->format('Y-m-d') ?: ''
        ), $previousPersonId);
    }
    private function __construct(
        string $message,
        public readonly int $previousPersonId
    ) {
        parent::__construct($message);
    }
}
