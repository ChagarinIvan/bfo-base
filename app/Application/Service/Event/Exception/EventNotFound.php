<?php

declare(strict_types=1);

namespace App\Application\Service\Event\Exception;

use App\Application\Exception\HttpError;
use RuntimeException;

#[HttpError(status: 404, code: 'event_not_found')]
final class EventNotFound extends RuntimeException
{
}
