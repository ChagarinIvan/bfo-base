<?php

declare(strict_types=1);

namespace App\Infrastracture\Laravel\Eloquent\Rank;

use App\Domain\Rank\Rank;
use App\Domain\Rank\RankRepository;

final class EloquentRankRepository implements RankRepository
{
    public function byId(int $id): ?Rank
    {
        return Rank::find($id);
    }
}
