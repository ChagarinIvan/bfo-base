<?php

declare(strict_types=1);

namespace App\Application\Service\Auth\Exception;

use App\Application\Exception\ApplicationException;
use App\Application\Exception\HttpError;

#[HttpError(status: 403, code: 'horizon_access_denied')]
final class HorizonAccessDenied extends ApplicationException
{
}
