<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Login;

use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SignInAction extends AbstractSignAction
{
    public function __invoke(Request $request): RedirectResponse
    {
        $authData = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        /** @var StatefulGuard $guard */
        $guard = $this->sessionGuard->guard('web');
        if ($guard->attempt($authData, true)) {
            $request->session()->regenerate();
            return $this->redirector->to('/app/competitions');
        }

        return $this->redirector->action(ShowLoginFormAction::class);
    }
}
