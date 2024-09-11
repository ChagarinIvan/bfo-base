<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Provider\Rank;

use App\Domain\Rank\RankRepository;
use App\Infrastracture\Laravel\Eloquent\Rank\EloquentRankRepository;
use Illuminate\Support\ServiceProvider;

final class RankProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->bind(RankRepository::class, EloquentRankRepository::class);
    }
}
