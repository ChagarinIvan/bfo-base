<?php

declare(strict_types=1);

namespace App\Infrastructure\Laravel\Eloquent\User;

use App\Domain\Auth\Factory\UserFactory;
use App\Domain\Auth\User;

final class EloquentUserFactory implements UserFactory
{
    public function create(string $email): User
    {
        $user = new User();
        $user->email = $email;

        return $user;
    }
}
