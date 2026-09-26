<?php

declare(strict_types=1);

namespace App\Domain\Cup\Exception;

use DomainException;

final class CupGroupNotSupported extends DomainException
{
    public function __construct(int $cupId, string $groupId)
    {
        parent::__construct("Cup {$cupId} does not support group {$groupId}.");
    }
}
