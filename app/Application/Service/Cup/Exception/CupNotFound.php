<?php

declare(strict_types=1);

namespace App\Application\Service\Cup\Exception;

use App\Application\Exception\ApplicationException;
use App\Application\Exception\HttpError;

#[HttpError(status: 404, code: 'cup_not_found')]
final class CupNotFound extends ApplicationException
{
}
