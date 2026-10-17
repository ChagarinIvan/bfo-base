<?php

declare(strict_types=1);

namespace App\Domain\ProtocolLine\Exception;

use DomainException;

final class UnableToActivateRank extends DomainException
{
    public function __construct()
    {
        parent::__construct('Activation date cannot be earlier than the protocol achievement date.');
    }
}
