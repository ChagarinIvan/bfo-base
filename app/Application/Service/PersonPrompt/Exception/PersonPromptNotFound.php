<?php

declare(strict_types=1);

namespace App\Application\Service\PersonPrompt\Exception;

use App\Application\Exception\ApplicationException;
use App\Application\Exception\HttpError;

#[HttpError(404, 'person_prompt_not_found')]
final class PersonPromptNotFound extends ApplicationException
{
}
