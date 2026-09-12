<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Provider\Distance;

use App\Domain\Distance\DistanceRepository;
use App\Infrastructure\Laravel\Eloquent\Distance\EloquentDistanceRepository;
use Illuminate\Support\ServiceProvider;

final class DistanceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(DistanceRepository::class, EloquentDistanceRepository::class);
    }
}
