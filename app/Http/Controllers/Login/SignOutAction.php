<?php

declare(strict_types=1);

namespace App\Http\Controllers\Login;

use Illuminate\Http\RedirectResponse;

class SignOutAction extends AbstractSignAction
{
    public function __invoke(): RedirectResponse
    {
        $this->authManager->guard('web')->logout();
        return $this->redirector->back();
    }
}
