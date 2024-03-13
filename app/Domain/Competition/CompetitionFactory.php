<?php

declare(strict_types=1);

namespace App\Domain\Competition;

use App\Models\Competition;

interface CompetitionFactory
{
    public function create(CompetitionInput $input): Competition;
}
