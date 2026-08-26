<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;

class VerifyCsrfToken extends PreventRequestForgery
{
}
