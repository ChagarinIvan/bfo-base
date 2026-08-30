<?php

declare(strict_types=1);

namespace App\Application\Service\Competition\Exception;

use App\Application\Exception\ApplicationException;
use App\Application\Exception\HttpError;

#[HttpError(status: 404, code: 'not_found')]
final class CompetitionNotFound extends ApplicationException
{
}
