<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Provider\ProtocolLine;

use App\Domain\ProtocolLine\ProtocolLineRepository;
use App\Repositories\ProtocolLinesRepository;
use Illuminate\Support\ServiceProvider;

final class ProtocolLineProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->bind(ProtocolLineRepository::class, ProtocolLinesRepository::class);
    }
}
