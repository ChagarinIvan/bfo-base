<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\TrimStrings as Middleware;

class TrimStrings extends Middleware
{
    protected $except = [
        'password',
        'password_confirmation',
    ];
}
