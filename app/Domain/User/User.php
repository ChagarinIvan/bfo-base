<?php

declare(strict_types=1);

namespace App\Domain\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @property int $id
 * @property string $email
 * @property string $password
 *
 * @method static Builder|User whereEmail(string $email)
 */
class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;

    public const SYSTEM_USER_ID = 10;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
