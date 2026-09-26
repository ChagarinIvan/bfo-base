<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Provider\Cup;

use App\Bridge\Laravel\Cache\CacheManagerCupsCacheInvalidator;
use App\Domain\Cup\CupCacheInvalidator;
use App\Domain\Cup\CupRepository;
use App\Domain\Cup\Factory\CupFactory;
use App\Domain\Cup\Factory\StandardCupFactory;
use App\Domain\Cup\Table\CupTableBuilder;
use App\Domain\Cup\Table\StandardCupTableBuilder;
use App\Infrastructure\Laravel\Cache\CachedCupTableBuilder;
use App\Infrastructure\Laravel\Eloquent\Cup\EloquentCupRepository;
use Illuminate\Cache\Repository as CacheManager;
use Illuminate\Support\ServiceProvider;

final class CupProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->bind(CupFactory::class, StandardCupFactory::class);
        $this->app->bind(CupRepository::class, EloquentCupRepository::class);
        $this->app->bind(CupCacheInvalidator::class, CacheManagerCupsCacheInvalidator::class);
        $this->app->bind(StandardCupTableBuilder::class);
        $this->app->bind(CupTableBuilder::class, fn (): CachedCupTableBuilder => new CachedCupTableBuilder(
            $this->app->make(CacheManager::class),
            $this->app->make(StandardCupTableBuilder::class),
        ));
    }
}
