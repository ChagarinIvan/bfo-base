<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Provider\Competition;

use App\Application\Handler\Event\DisableCompetitionHandler;
use App\Domain\Competition\CompetitionRepository;
use App\Domain\Competition\Factory\CompetitionFactory;
use App\Domain\Competition\StandardCompetitionFactory;
use App\Infrastracture\Laravel\Eloquent\Competition\EloquentCompetitionRepository;
use Illuminate\Support\ServiceProvider;

final class CompetitionProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->bind(CompetitionFactory::class, StandardCompetitionFactory::class);
        $this->app->bind(CompetitionRepository::class, EloquentCompetitionRepository::class);
    }
}
