<?php

declare(strict_types=1);

namespace App\Application\Service\Group\Exception;

use App\Application\Exception\ApplicationException;
use App\Application\Exception\HttpError;

#[HttpError(status: 409, code: 'cannot_merge_same_group')]
final class CannotMergeSameGroup extends ApplicationException
{
}
