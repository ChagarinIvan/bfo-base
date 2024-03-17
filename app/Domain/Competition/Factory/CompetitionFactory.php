<?php

declare(strict_types=1);

namespace App\Domain\Competition\Factory;

use App\Domain\Competition\Competition;

interface CompetitionFactory
{
    public function create(CompetitionInput $input): Competition;
}
