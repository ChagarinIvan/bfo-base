<?php

declare(strict_types=1);

namespace App\Application\Service\Event\Exception;

use App\Application\Exception\ApplicationException;
use App\Application\Exception\HttpError;

#[HttpError(status: 404, code: 'event_not_found')]
final class EventNotFound extends ApplicationException
{
}
