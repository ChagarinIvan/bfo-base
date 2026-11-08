<?php

declare(strict_types=1);
namespace App\Application\Service\Person\ManualRank\Exception;
use App\Application\Exception\ApplicationException;
use App\Application\Exception\HttpError;
#[HttpError(status: 422, code: 'invalid_manual_rank_dates')]
final class InvalidManualRankDates extends ApplicationException
{
}
