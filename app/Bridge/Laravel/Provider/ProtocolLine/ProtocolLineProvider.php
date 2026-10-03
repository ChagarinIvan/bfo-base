<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Provider\ProtocolLine;

use App\Domain\ProtocolLine\Factory\ProtocolLinesFactory;
use App\Domain\ProtocolLine\Factory\StandardProtocolLinesFactory;
use App\Domain\ProtocolLine\ProtocolLineIdentifier;
use App\Domain\ProtocolLine\ProtocolLineOperations;
use App\Domain\ProtocolLine\ProtocolLineRepository;
use App\Domain\ProtocolLine\StandardProtocolLineIdentifier;
use App\Infrastructure\Laravel\Eloquent\ProtocolLine\EloquentProtocolLineOperations;
use App\Infrastructure\Laravel\Eloquent\ProtocolLine\EloquentProtocolLinesRepository;
use Illuminate\Support\ServiceProvider;

final class ProtocolLineProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->bind(ProtocolLinesFactory::class, StandardProtocolLinesFactory::class);
        $this->app->bind(ProtocolLineRepository::class, EloquentProtocolLinesRepository::class);
        $this->app->bind(ProtocolLineOperations::class, EloquentProtocolLineOperations::class);
        $this->app->bind(ProtocolLineIdentifier::class, StandardProtocolLineIdentifier::class);
    }
}
