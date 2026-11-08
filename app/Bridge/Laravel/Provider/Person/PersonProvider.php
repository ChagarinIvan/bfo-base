<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Provider\Person;

use App\Domain\Person\Factory\PersonFactory;
use App\Domain\Person\Factory\PreventDuplicatePersonFactory;
use App\Domain\Person\Factory\StandardPersonFactory;
use App\Domain\Person\ManualRank\ManualPersonRankRepository;
use App\Domain\Person\PersonRepository;
use App\Infrastructure\Laravel\Eloquent\Person\EloquentPersonRepository;
use App\Infrastructure\Laravel\Eloquent\Person\ManualRank\SqlManualPersonRankRepository;
use Illuminate\Support\ServiceProvider;

final class PersonProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->bind(ManualPersonRankRepository::class, SqlManualPersonRankRepository::class);
        $this->app->bind(PersonRepository::class, EloquentPersonRepository::class);
        $this->app->bind(StandardPersonFactory::class, StandardPersonFactory::class);

        $this->app->bind(PersonFactory::class, fn (): PreventDuplicatePersonFactory => new PreventDuplicatePersonFactory(
            $this->app->get(StandardPersonFactory::class),
            $this->app->get(PersonRepository::class),
        ));
    }
}
