<?php

declare(strict_types=1);

namespace App\Http\Controllers\Registration;

use App\Http\Controllers\AbstractRedirectAction;
use App\Http\Controllers\Login\TokenAuthAction;
use App\Mail\RegistrationUrlMail;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Encryption\Encrypter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;

class SendRegistrationDataAction extends AbstractRedirectAction
{
    private Encrypter $encrypter;
    private Mailer $mailer;
    private UrlGenerator $urlGenerator;

    public function __construct(Redirector $redirector, Encrypter $encrypter, Mailer $mailer, UrlGenerator $urlGenerator)
    {
        parent::__construct($redirector);
        $this->encrypter = $encrypter;
        $this->mailer = $mailer;
        $this->urlGenerator = $urlGenerator;
    }

    public function __invoke(Request $request): RedirectResponse
    {
        $form = $request->validate([
            'email' => 'required|email',
        ]);

        $email = $form['email'];
        $token = $this->encrypter->encrypt($email);
        $this->mailer->send(new RegistrationUrlMail($email, $this->urlGenerator->action(TokenAuthAction::class, ['token' => $token])));

        return $this->redirector->to('competitions/y0');
    }
}
