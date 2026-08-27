<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\TrimStrings as Middleware;
use Override;

class TrimStrings extends Middleware
{
    #[Override]
    protected $except = [
        'password',
        'password_confirmation',
    ];
}
