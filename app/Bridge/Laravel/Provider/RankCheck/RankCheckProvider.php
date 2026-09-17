<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Provider\RankCheck;

use App\Domain\RankCheck\Factory\RankCheckFactory;
use App\Domain\RankCheck\Factory\RankCheckRowFactory;
use App\Domain\RankCheck\Factory\StandardRankCheckFactory;
use App\Domain\RankCheck\Factory\StandardRankCheckRowFactory;
use App\Domain\RankCheck\RankCheckPersonMatcher;
use App\Domain\RankCheck\RankCheckPersonSnapshotReader;
use App\Domain\RankCheck\RankCheckProcessor;
use App\Domain\RankCheck\RankCheckRepository;
use App\Domain\RankCheck\RankCheckRowRepository;
use App\Domain\RankCheck\RankListParser;
use App\Domain\RankCheck\StandardRankCheckPersonMatcher;
use App\Domain\RankCheck\StandardRankCheckProcessor;
use App\Domain\RankCheck\StandardRankListParser;
use App\Infrastructure\Laravel\Eloquent\RankCheck\EloquentRankCheckPersonSnapshotReader;
use App\Infrastructure\Laravel\Eloquent\RankCheck\EloquentRankCheckRepository;
use App\Infrastructure\Laravel\Eloquent\RankCheck\EloquentRankCheckRowRepository;
use Illuminate\Support\ServiceProvider;

final class RankCheckProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->bind(RankCheckRepository::class, EloquentRankCheckRepository::class);
        $this->app->bind(RankCheckFactory::class, StandardRankCheckFactory::class);
        $this->app->bind(RankCheckRowFactory::class, StandardRankCheckRowFactory::class);
        $this->app->bind(RankCheckRowRepository::class, EloquentRankCheckRowRepository::class);
        $this->app->bind(RankListParser::class, StandardRankListParser::class);
        $this->app->bind(RankCheckPersonMatcher::class, StandardRankCheckPersonMatcher::class);
        $this->app->bind(RankCheckPersonSnapshotReader::class, EloquentRankCheckPersonSnapshotReader::class);
        $this->app->bind(RankCheckProcessor::class, StandardRankCheckProcessor::class);
    }
}
