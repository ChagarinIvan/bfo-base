<?php

declare(strict_types=1);

namespace App\Application\Service\RankCheck\Exception;

use App\Application\Exception\ApplicationException;
use App\Application\Exception\HttpError;
use Throwable;

#[HttpError(status: 400, code: 'invalid_rank_check_list')]
final class InvalidRankCheckList extends ApplicationException
{
    public function __construct(?Throwable $previous = null)
    {
        parent::__construct('Invalid rank check list.', previous: $previous);
    }
}
