<?php

namespace App\Http\Controllers\Login;

use App\Http\Controllers\AbstractRedirectAction;
use App\Http\Controllers\Competition\ShowCompetitionsListAction;
use App\Models\Year;
use \Illuminate\Auth\SessionGuard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Http\Request;

class AuthValidationAction extends AbstractRedirectAction
{
    private SessionGuard $sessionGuard;

    public function __construct(Redirector $redirector, SessionGuard $sessionGuard)
    {
        parent::__construct($redirector);
        $this->sessionGuard = $sessionGuard;
    }

    public function __invoke(Request $request): RedirectResponse
    {
        $authData = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($this->sessionGuard->attempt($authData, true)) {
            $request->session()->regenerate();
            return $this->redirector->action(ShowCompetitionsListAction::class, Year::actualYear());
        }

        return $this->redirector->action(ShowLoginFormAction::class);
    }
}
