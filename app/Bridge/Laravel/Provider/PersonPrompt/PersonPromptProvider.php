<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Provider\PersonPrompt;

use App\Domain\PersonPrompt\PersonPromptRepository;
use App\Infrastracture\Laravel\Eloquent\PersonPrompt\EloquentPromptPaymentRepository;
use Illuminate\Support\ServiceProvider;

final class PersonPromptProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->bind(PersonPromptRepository::class, EloquentPromptPaymentRepository::class);
    }
}
