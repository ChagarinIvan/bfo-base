<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Provider\Rank;

use App\Domain\Person\RankFactsCollector;
use App\Infrastructure\Laravel\Eloquent\Rank\EloquentRankFactsCollector;
use Illuminate\Support\ServiceProvider;

final class RankProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->bind(RankFactsCollector::class, EloquentRankFactsCollector::class);
    }
}
