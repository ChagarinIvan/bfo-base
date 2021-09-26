<?php

namespace App\Repositories;

use App\Models\Competition;

class CompetitionsRepository
{
    public function findCompetition(int $competitionId): ?Competition
    {
        return Competition::find($competitionId);
    }
}
