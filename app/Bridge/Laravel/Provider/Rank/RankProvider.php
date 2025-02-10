<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Provider\Rank;

use App\Domain\Rank\Factory\RankFactory;
use App\Domain\Rank\Factory\StandardRankFactory;
use App\Domain\Rank\JuniorThirdRankChecker;
use App\Domain\Rank\RankRepository;
use App\Domain\Rank\StandardJuniorJuniorThirdRankChecker;
use App\Infrastracture\Laravel\Eloquent\Rank\EloquentRankRepository;
use Illuminate\Support\ServiceProvider;

final class RankProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->bind(RankRepository::class, EloquentRankRepository::class);
        $this->app->bind(RankFactory::class, StandardRankFactory::class);
        $this->app->bind(JuniorThirdRankChecker::class, StandardJuniorJuniorThirdRankChecker::class);
        $this->app->bind(JuniorThirdRankChecker::class, StandardJuniorJuniorThirdRankChecker::class);
    }
}
