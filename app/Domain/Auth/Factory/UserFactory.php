<?php

declare(strict_types=1);

namespace App\Domain\Auth\Factory;

use App\Domain\Auth\User;

interface UserFactory
{
    public function create(string $email): User;
}
