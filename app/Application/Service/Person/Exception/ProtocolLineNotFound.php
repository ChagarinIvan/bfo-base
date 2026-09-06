<?php

declare(strict_types=1);

namespace App\Application\Service\Person\Exception;

use App\Application\Exception\ApplicationException;
use App\Application\Exception\HttpError;

#[HttpError(status: 404, code: 'protocol_line_not_found')]
final class ProtocolLineNotFound extends ApplicationException
{
}
