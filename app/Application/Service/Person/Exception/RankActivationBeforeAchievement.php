<?php

declare(strict_types=1);

namespace App\Application\Service\Person\Exception;

use App\Application\Exception\ApplicationException;
use App\Application\Exception\HttpError;

#[HttpError(status: 422, code: 'rank_activation_before_achievement')]
final class RankActivationBeforeAchievement extends ApplicationException
{
    public function __construct()
    {
        parent::__construct('Activation date cannot be earlier than the protocol achievement date.');
    }
}
