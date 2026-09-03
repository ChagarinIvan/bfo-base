<?php

declare(strict_types=1);

namespace App\Application\Service\Group\Exception;

use App\Application\Exception\ApplicationException;
use App\Application\Exception\HttpError;

#[HttpError(status: 404, code: 'group_not_found')]
final class GroupNotFound extends ApplicationException
{
}
