<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Provider\Person;

use App\Domain\Person\Factory\PersonFactory;
use App\Domain\Person\Factory\PreventDuplicatePersonFactory;
use App\Domain\Person\Factory\StandardPersonFactory;
use App\Domain\Person\PersonRepository;
use App\Infrastracture\Laravel\Eloquent\Person\EloquentPersonRepository;
use Illuminate\Support\ServiceProvider;

final class PersonProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->bind(PersonRepository::class, EloquentPersonRepository::class);
        $this->app->bind(StandardPersonFactory::class, StandardPersonFactory::class);

        $this->app->bind(PersonFactory::class, fn () => new PreventDuplicatePersonFactory(
            $this->app->get(StandardPersonFactory::class),
            $this->app->get(PersonRepository::class),
        ));
    }
}
