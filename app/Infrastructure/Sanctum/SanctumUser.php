<?php

declare(strict_types=1);

namespace App\Infrastructure\Sanctum;

use Illuminate\Database\Eloquent\Attributes\Table;
use App\Domain\Auth\User as DomainUser;
use Laravel\Sanctum\HasApiTokens;

#[Table(name: 'users')]
final class SanctumUser extends DomainUser
{
    use HasApiTokens;
}
