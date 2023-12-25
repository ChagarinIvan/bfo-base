<?php

declare(strict_types=1);

namespace App\Http\Controllers\Login;

use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\RedirectResponse;

class SignOutAction extends AbstractSignAction
{
    public function __invoke(): RedirectResponse
    {
        /** @var StatefulGuard $guard */
        $guard = $this->sessionGuard->guard('web');
        $guard->logout();

        return $this->redirector->back();
    }
}
