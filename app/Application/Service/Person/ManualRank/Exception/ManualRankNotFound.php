<?php

declare(strict_types=1);
namespace App\Application\Service\Person\ManualRank\Exception;
use App\Application\Exception\ApplicationException;
use App\Application\Exception\HttpError;
#[HttpError(status: 404, code: 'manual_rank_not_found')]
final class ManualRankNotFound extends ApplicationException
{
}
