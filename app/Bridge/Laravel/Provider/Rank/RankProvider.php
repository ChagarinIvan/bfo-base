<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Provider\Rank;

use App\Domain\Rank\RankFacts;
use App\Infrastructure\Laravel\Eloquent\Rank\EloquentRankFacts;
use Illuminate\Support\ServiceProvider;

final class RankProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->bind(RankFacts::class, EloquentRankFacts::class);
    }
}
