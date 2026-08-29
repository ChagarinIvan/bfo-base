<?php

declare(strict_types=1);

namespace App\Domain\Auth\Exception;

use DomainException;

final class InvalidCredentials extends DomainException
{
    public function __construct()
    {
        parent::__construct('The provided credentials are incorrect.');
    }
}
