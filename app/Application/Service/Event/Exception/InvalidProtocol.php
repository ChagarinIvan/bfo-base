<?php

declare(strict_types=1);

namespace App\Application\Service\Event\Exception;

use App\Application\Exception\ApplicationException;
use App\Application\Exception\HttpError;
use Throwable;

#[HttpError(status: 400, code: 'invalid_protocol')]
final class InvalidProtocol extends ApplicationException
{
    public function __construct(?Throwable $previous = null)
    {
        parent::__construct('wrong protocol content', previous: $previous);
    }
}
