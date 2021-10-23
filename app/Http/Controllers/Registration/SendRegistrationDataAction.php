<?php

declare(strict_types=1);

namespace App\Http\Controllers\Registration;

use App\Http\Controllers\AbstractAction;
use App\Http\Controllers\Competition\ShowCompetitionsListAction;
use App\Http\Controllers\Login\MakeNewPasswordByTokenAction;
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
    private Encrypter $encrypter;
    private Mailer $mailer;
    private UrlGenerator $urlGenerator;

    public function __construct(
        ViewActionsService $viewService,
        Redirector $redirector,
        Encrypter $encrypter,
        Mailer $mailer,
        UrlGenerator $urlGenerator
    ) {
        parent::__construct($viewService, $redirector);
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
        $this->mailer->send(new RegistrationUrlMail($email, $this->urlGenerator->action(MakeNewPasswordByTokenAction::class, ['token' => $token])));

        return $this->redirector->action(ShowCompetitionsListAction::class, [Year::actualYear()]);
    }
}
