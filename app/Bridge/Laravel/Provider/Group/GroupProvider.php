<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Provider\Group;

use App\Domain\Distance\DistanceMover;
use App\Domain\Group\GroupRepository;
use App\Domain\Group\GroupUpdater;
use App\Domain\Group\PreventDuplicateGroupUpdater;
use App\Domain\Group\StandardGroupUpdater;
use App\Infrastructure\Laravel\Eloquent\Distance\EloquentDistanceMover;
use App\Infrastructure\Laravel\Eloquent\Group\EloquentGroupRepository;
use Illuminate\Support\ServiceProvider;

final class GroupProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(GroupRepository::class, EloquentGroupRepository::class);
        $this->app->bind(DistanceMover::class, EloquentDistanceMover::class);
        $this->app->bind(StandardGroupUpdater::class, StandardGroupUpdater::class);

        $this->app->bind(GroupUpdater::class, fn (): PreventDuplicateGroupUpdater => new PreventDuplicateGroupUpdater(
            $this->app->get(StandardGroupUpdater::class),
            $this->app->get(GroupRepository::class),
        ));
    }
}
