<?php

declare(strict_types=1);

namespace App\Application\Service\Cup\Exception;

use App\Application\Exception\ApplicationException;
use App\Application\Exception\HttpError;
use App\Domain\Cup\Exception\CupGroupNotSupported;

#[HttpError(status: 400, code: 'cup_group_not_supported')]
final class UnsupportedCupGroup extends ApplicationException
{
    public function __construct(CupGroupNotSupported $previous)
    {
        parent::__construct($previous->getMessage(), previous: $previous);
    }
}
