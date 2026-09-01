<?php

declare(strict_types=1);

namespace App\Application\Service\Club\Exception;

use App\Application\Exception\ApplicationException;
use App\Application\Exception\HttpError;
use App\Domain\Club\Exception\ClubAlreadyExist;

#[HttpError(status: 409, code: 'club_name_already_exists')]
final class FailedToUpdateClub extends ApplicationException
{
    public static function dueError(ClubAlreadyExist $exception): self
    {
        return new self($exception->getMessage(), previous: $exception);
    }
}
