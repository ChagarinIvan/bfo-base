<?php

namespace App\Http\Controllers\Login;

use App\Http\Controllers\Competition\ShowCompetitionsListAction;
use App\Models\Year;
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

        if ($this->sessionGuard->guard('web')->attempt($authData, true)) {
            $request->session()->regenerate();
            return $this->redirector->action(ShowCompetitionsListAction::class, [(string)Year::actualYear()->value]);
        }

        return $this->redirector->action(ShowLoginFormAction::class);
    }
}
