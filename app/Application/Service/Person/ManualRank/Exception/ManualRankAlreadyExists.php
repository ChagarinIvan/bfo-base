<?php

declare(strict_types=1);

namespace App\Application\Service\Person\ManualRank\Exception;

use App\Application\Exception\ApplicationException;
use App\Application\Exception\HttpError;

#[HttpError(status: 409, code: 'manual_rank_already_exists')]
final class ManualRankAlreadyExists extends ApplicationException
{
}
