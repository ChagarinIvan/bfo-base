<?php

namespace App\Http\Controllers\Login;

use App\Http\Controllers\AbstractRedirectAction;
use App\Http\Controllers\Competition\ShowCompetitionsListAction;
use App\Models\Year;
use App\Services\BackUrlService;
use Illuminate\Auth\AuthManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Http\Request;

class AuthValidationAction extends AbstractRedirectAction
{
    private AuthManager $authManager;

    public function __construct(
        Redirector $redirector,
        BackUrlService $backUrlService,
        AuthManager $sessionGuard
    ) {
        parent::__construct($redirector, $backUrlService);
        $this->authManager = $sessionGuard;
    }

    public function __invoke(Request $request): RedirectResponse
    {
        $authData = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($this->authManager->guard('web')->attempt($authData, true)) {
            $request->session()->regenerate();
            return $this->redirector->action(ShowCompetitionsListAction::class, Year::actualYear());
        }

        return $this->redirector->action(ShowLoginFormAction::class);
    }
}
