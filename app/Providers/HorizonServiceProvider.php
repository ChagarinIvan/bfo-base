<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Http\Request;
use Laravel\Horizon\Horizon;
use Laravel\Horizon\HorizonApplicationServiceProvider;

final class HorizonServiceProvider extends HorizonApplicationServiceProvider
{
    public function boot(): void
    {
        parent::boot();

        $config = $this->app->make(ConfigRepository::class);
        Horizon::auth(static fn(Request $request): bool => (int) $request->user()?->getAuthIdentifier() === (int) $config->get('horizon.authorized_user_id'));
    }
}
