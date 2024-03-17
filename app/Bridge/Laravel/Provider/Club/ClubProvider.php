<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Provider\Club;

use App\Domain\Club\ClubRepository;
use App\Domain\Club\Factory\ClubFactory;
use App\Domain\Club\Factory\PreventDuplicateClubFactory;
use App\Domain\Club\Factory\StandardClubFactory;
use App\Infrastracture\Laravel\Eloquent\Club\EloquentClubRepository;
use Illuminate\Support\ServiceProvider;

final class ClubProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->bind(ClubRepository::class, EloquentClubRepository::class);

        $this->app->bind(StandardClubFactory::class, StandardClubFactory::class);

        $this->app->bind(ClubFactory::class, fn () => new PreventDuplicateClubFactory(
            $this->app->get(StandardClubFactory::class),
            $this->app->get(ClubRepository::class),
        ));
    }
}
