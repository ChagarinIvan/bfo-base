<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Provider\Cup;

use App\Domain\Cup\CupRepository;
use App\Domain\Cup\Factory\CupFactory;
use App\Domain\Cup\Factory\StandardCupFactory;
use App\Infrastracture\Laravel\Eloquent\Cup\EloquentCupRepository;
use Illuminate\Support\ServiceProvider;

final class CupProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->bind(CupFactory::class, StandardCupFactory::class);
        $this->app->bind(CupRepository::class, EloquentCupRepository::class);
    }
}
