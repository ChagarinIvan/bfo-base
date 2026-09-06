<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Provider\ProtocolLine;

use App\Domain\ProtocolLine\ProtocolLineOperations;
use App\Domain\ProtocolLine\ProtocolLineRepository;
use App\Infrastructure\Laravel\Eloquent\ProtocolLine\EloquentProtocolLinesRepository;
use App\Services\ProtocolLineService;
use Illuminate\Support\ServiceProvider;

final class ProtocolLineProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->bind(ProtocolLineRepository::class, EloquentProtocolLinesRepository::class);
        $this->app->bind(ProtocolLineOperations::class, ProtocolLineService::class);
    }
}
