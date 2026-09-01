<?php

declare(strict_types=1);

namespace App\Application\Service\Club\Exception;

use App\Application\Exception\ApplicationException;
use App\Application\Exception\HttpError;
use App\Domain\Club\Exception\ClubAlreadyExist;
use function sprintf;

#[HttpError(status: 409, code: 'club_name_already_exists')]
final class FailedToAddClub extends ApplicationException
{
    public static function dueError(ClubAlreadyExist $exception): self
    {
        return new self(sprintf('Unable to add club. Reason: %s', $exception->getMessage()), previous: $exception);
    }
}
