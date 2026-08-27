<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Provider\CupEvent;

use App\Domain\Cup\CupEvent\CupEventRepository;
use App\Infrastructure\Laravel\Eloquent\CupEvent\EloquentCupEventRepository;
use Illuminate\Support\ServiceProvider;

final class CupEventProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->bind(CupEventRepository::class, EloquentCupEventRepository::class);
    }
}
