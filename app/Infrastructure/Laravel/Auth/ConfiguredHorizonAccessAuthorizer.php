<?php

declare(strict_types=1);

namespace App\Infrastructure\Laravel\Auth;

use App\Domain\Auth\HorizonAccessAuthorizer;
use Illuminate\Contracts\Config\Repository as ConfigRepository;

final readonly class ConfiguredHorizonAccessAuthorizer implements HorizonAccessAuthorizer
{
    public function __construct(private ConfigRepository $config)
    {
    }

    public function canAccess(int $userId): bool
    {
        return $userId === (int) $this->config->get('horizon.authorized_user_id');
    }
}
