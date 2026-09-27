<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Provider\CupEvent;

use App\Domain\Cup\CupEvent\CupEventRepository;
use App\Domain\Cup\CupEvent\CupEventUpdater;
use App\Domain\Cup\CupEvent\Factory\CupEventFactory;
use App\Domain\Cup\CupEvent\Factory\StandardCupEventFactory;
use App\Domain\Cup\CupEvent\Factory\ValidateCupEventFactory;
use App\Domain\Cup\CupEvent\StandardCupEventUpdater;
use App\Domain\Cup\CupRepository;
use App\Domain\Event\EventRepository;
use App\Infrastructure\Laravel\Eloquent\CupEvent\EloquentCupEventRepository;
use Illuminate\Support\ServiceProvider;

final class CupEventProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->bind(CupEventRepository::class, EloquentCupEventRepository::class);
        $this->app->bind(CupEventUpdater::class, StandardCupEventUpdater::class);
        $this->app->bind(StandardCupEventFactory::class, StandardCupEventFactory::class);
        $this->app->bind(CupEventFactory::class, fn (): ValidateCupEventFactory => new ValidateCupEventFactory(
            $this->app->get(StandardCupEventFactory::class),
            $this->app->get(CupRepository::class),
            $this->app->get(EventRepository::class),
            $this->app->get(CupEventRepository::class),
        ));
    }
}
