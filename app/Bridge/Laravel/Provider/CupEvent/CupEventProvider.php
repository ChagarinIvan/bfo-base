<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Provider\CupEvent;

use App\Domain\Cup\CupRepository;
use App\Domain\Cup\Factory\CupFactory;
use App\Domain\Cup\Factory\StandardCupFactory;
use App\Domain\CupEvent\CupEventRepository;
use App\Infrastracture\Laravel\Eloquent\Cup\EloquentCupRepository;
use App\Infrastracture\Laravel\Eloquent\CupEvent\EloquentCupEventRepository;
use Illuminate\Support\ServiceProvider;

final class CupEventProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->bind(CupEventRepository::class, EloquentCupEventRepository::class);
    }
}
