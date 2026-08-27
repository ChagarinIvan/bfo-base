<?php

declare(strict_types=1);

namespace App\Infrastructure\Laravel\Eloquent\User;

use App\Domain\User\User;
use App\Domain\User\UserRepository;

final class EloquentUserRepository implements UserRepository
{
    public function byId(int $id): ?User
    {
        return User::find($id);
    }
}
