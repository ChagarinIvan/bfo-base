<?php

declare(strict_types=1);

namespace App\Application\Service\PersonPrompt\Exception;

use App\Application\Exception\ApplicationException;
use App\Application\Exception\HttpError;

#[HttpError(404, 'person_not_found')]
final class PersonNotFound extends ApplicationException
{
}
