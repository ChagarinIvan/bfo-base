<?php

declare(strict_types=1);

namespace App\Infrastructure\Laravel\Eloquent\User;

use App\Domain\Auth\User;
use App\Domain\Auth\UserRepository;

final class EloquentUserRepository implements UserRepository
{
    public function all(): array
    {
        return User::query()->get()->all();
    }

    public function byId(int $id): ?User
    {
        return User::find($id);
    }
}
