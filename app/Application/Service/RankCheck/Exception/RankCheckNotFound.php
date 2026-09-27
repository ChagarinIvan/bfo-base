<?php

declare(strict_types=1);

namespace App\Application\Service\RankCheck\Exception;

use App\Application\Exception\ApplicationException;
use App\Application\Exception\HttpError;

#[HttpError(status: 404, code: 'rank_check_not_found')]
final class RankCheckNotFound extends ApplicationException
{
}
