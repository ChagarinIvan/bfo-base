<?php

declare(strict_types=1);

namespace App\Infrastracture\Laravel\Eloquent\Competition;

use App\Domain\Competition\CompetitionRepository;
use App\Models\Competition;

final class EloquentCompetitionRepository implements CompetitionRepository
{
    public function add(Competition $competition): void
    {
        $competition->save();
    }
}
