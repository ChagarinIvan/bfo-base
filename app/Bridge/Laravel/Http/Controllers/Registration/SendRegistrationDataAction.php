<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Registration;

use App\Bridge\Laravel\Http\Controllers\AbstractAction;
use App\Bridge\Laravel\Http\Controllers\Competition\ShowCompetitionsListAction;
use App\Bridge\Laravel\Http\Controllers\Login\MakeNewPasswordByTokenAction;
use App\Mail\RegistrationUrlMail;
use App\Models\Year;
use App\Services\ViewActionsService;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Encryption\Encrypter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;

class SendRegistrationDataAction extends AbstractAction
{
    public function __construct(
        protected ViewActionsService $viewService,
        protected Redirector $redirector,
        protected Encrypter $encrypter,
        protected Mailer $mailer,
        protected UrlGenerator $urlGenerator
    ) {
        parent::__construct($viewService, $redirector);
    }

    public function __invoke(Request $request): RedirectResponse
    {
        $form = $request->validate([
            'email' => 'required|email',
        ]);

        $email = $form['email'];
        $token = $this->encrypter->encrypt($email);
        $this->mailer->send(new RegistrationUrlMail($email, $this->urlGenerator->action(MakeNewPasswordByTokenAction::class, ['token' => $token])));

        return $this->redirector->action(ShowCompetitionsListAction::class, ['year' => (string) Year::actualYear()->value]);
    }
}
