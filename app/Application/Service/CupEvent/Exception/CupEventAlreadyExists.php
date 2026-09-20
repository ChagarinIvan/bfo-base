<?php

declare(strict_types=1);

namespace App\Application\Service\CupEvent\Exception;

use App\Application\Exception\ApplicationException;
use App\Application\Exception\HttpError;

#[HttpError(status: 422, code: 'cup_event_already_exists')]
final class CupEventAlreadyExists extends ApplicationException
{
}
