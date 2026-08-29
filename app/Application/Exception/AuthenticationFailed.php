<?php

declare(strict_types=1);

namespace App\Application\Exception;

use Throwable;

#[HttpError(status: 401, code: 'invalid_credentials')]
final class AuthenticationFailed extends ApplicationException
{
    public function __construct(?Throwable $previous = null)
    {
        parent::__construct('The provided credentials are incorrect.', previous: $previous);
    }
}
