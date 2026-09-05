<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Provider\PersonPrompt;

use App\Domain\PersonPrompt\Factory\PersonPromptFactory;
use App\Domain\PersonPrompt\Factory\StandardPersonPromptFactory;
use App\Domain\PersonPrompt\PersonPromptGenerator;
use App\Domain\PersonPrompt\PersonPromptMetaphone;
use App\Domain\PersonPrompt\PersonPromptRepository;
use App\Domain\PersonPrompt\StandardPersonPromptGenerator;
use App\Domain\PersonPrompt\TranslitPersonPromptMetaphone;
use App\Domain\Shared\NameNormalizer;
use App\Domain\Shared\StandardNameNormalizer;
use App\Infrastructure\Laravel\Eloquent\PersonPrompt\EloquentPromptPaymentRepository;
use Illuminate\Support\ServiceProvider;

final class PersonPromptProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->bind(PersonPromptFactory::class, StandardPersonPromptFactory::class);
        $this->app->bind(PersonPromptRepository::class, EloquentPromptPaymentRepository::class);
        $this->app->bind(NameNormalizer::class, StandardNameNormalizer::class);
        $this->app->bind(PersonPromptGenerator::class, StandardPersonPromptGenerator::class);
        $this->app->bind(PersonPromptMetaphone::class, TranslitPersonPromptMetaphone::class);
    }
}
