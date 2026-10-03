<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Provider\Rank;

use App\Domain\Person\EventPersonRankUpdater;
use App\Domain\Person\RankFactsCollector;
use App\Domain\Person\StandardEventPersonRankUpdater;
use App\Domain\Rank\RepeatedMasterRankActivator;
use App\Infrastructure\Laravel\Eloquent\Rank\EloquentRankFactsCollector;
use App\Infrastructure\Laravel\Eloquent\Rank\EloquentRepeatedMasterRankActivator;
use Illuminate\Support\ServiceProvider;

final class RankProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->bind(RankFactsCollector::class, EloquentRankFactsCollector::class);
        $this->app->bind(EventPersonRankUpdater::class, StandardEventPersonRankUpdater::class);
        $this->app->bind(RepeatedMasterRankActivator::class, EloquentRepeatedMasterRankActivator::class);
    }
}
