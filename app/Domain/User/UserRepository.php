<?php

declare(strict_types=1);

namespace App\Domain\User;

use App\Models\User;

interface UserRepository
{
    public function byId(int $id): ?User;
}
