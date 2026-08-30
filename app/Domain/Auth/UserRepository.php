<?php

declare(strict_types=1);

namespace App\Domain\Auth;

interface UserRepository
{
    /** @return User[] */
    public function all(): array;

    public function byId(int $id): ?User;
}
