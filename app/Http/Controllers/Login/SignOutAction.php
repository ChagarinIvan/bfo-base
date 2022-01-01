<?php

namespace App\Http\Controllers\Login;

use Illuminate\Http\RedirectResponse;

class SignOutAction extends AbstractSignAction
{
    public function __invoke(): RedirectResponse
    {
        $this->sessionGuard->guard('web')->logout();
        return $this->redirector->back();
    }
}
