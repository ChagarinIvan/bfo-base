<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Provider\PersonPayment;

use App\Domain\PersonPayment\Factory\PersonPaymentFactory;
use App\Domain\PersonPayment\PersonPaymentRepository;
use App\Domain\PersonPayment\StandardPersonPaymentFactory;
use App\Infrastracture\Laravel\Eloquent\PersonPayment\EloquentPersonPaymentRepository;
use Illuminate\Support\ServiceProvider;

final class PersonPaymentProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->bind(PersonPaymentRepository::class, EloquentPersonPaymentRepository::class);
        $this->app->bind(PersonPaymentFactory::class, StandardPersonPaymentFactory::class);
    }
}
