<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Provider\Club;

use App\Domain\Club\ClubRepository;
use App\Domain\Club\ClubUpdater;
use App\Domain\Club\Factory\ClubFactory;
use App\Domain\Club\Factory\PreventDuplicateClubFactory;
use App\Domain\Club\Factory\StandardClubFactory;
use App\Domain\Club\PreventDuplicateClubUpdater;
use App\Domain\Club\StandardClubUpdater;
use App\Infrastructure\Laravel\Eloquent\Club\EloquentClubRepository;
use Illuminate\Support\ServiceProvider;

final class ClubProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->bind(ClubRepository::class, EloquentClubRepository::class);

        $this->app->bind(StandardClubFactory::class, StandardClubFactory::class);

        $this->app->bind(ClubFactory::class, fn (): PreventDuplicateClubFactory => new PreventDuplicateClubFactory(
            $this->app->get(StandardClubFactory::class),
            $this->app->get(ClubRepository::class),
        ));

        $this->app->bind(StandardClubUpdater::class, StandardClubUpdater::class);
        $this->app->bind(ClubUpdater::class, fn (): PreventDuplicateClubUpdater => new PreventDuplicateClubUpdater(
            $this->app->get(StandardClubUpdater::class),
            $this->app->get(ClubRepository::class),
        ));
    }
}
