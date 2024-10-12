<?php

declare(strict_types=1);

namespace App\Domain\Rank;

use App\Domain\Competition\Competition;
use App\Domain\Cup\Cup;
use App\Domain\Shared\Criteria;
use Illuminate\Support\Collection;

interface RankRepository
{
    public function byId(int $id): ?Rank;
}
