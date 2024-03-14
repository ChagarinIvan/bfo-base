<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Controllers\Login;

use App\Bridge\Laravel\Http\Controllers\AbstractAction;
use App\Mail\PasswordMail;
use App\Models\User;
use App\Services\ViewActionsService;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Hashing\HashManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Str;
use Illuminate\Validation\Factory as Validator;

class MakeNewPasswordByTokenAction extends AbstractAction
{
    public function __construct(
        protected ViewActionsService $viewService,
        protected Redirector $redirector,
        private Encrypter $encrypter,
        private Validator $validator,
        private HashManager $hashManager,
        private Mailer $mailer
    ) {
        parent::__construct($viewService, $redirector);
    }

    public function __invoke(string $token): RedirectResponse
    {
        $email = $this->encrypter->decrypt($token);
        if (!$this->validator->validate(['email' => $email], ['email' => 'required|email'])) {
            return $this->redirectToError();
        }

        $users = User::whereEmail($email)->get();
        if ($users->isEmpty()) {
            $user = new User();
            $user->email = $email;
        } else {
            $user = $users->first();
        }

        $password = Str::random(8);
        $user->password = $this->hashManager->make($password);
        $user->save();
        $this->mailer->send(new PasswordMail($email, $password));

        return $this->redirector->action(ShowLoginFormAction::class);
    }
}
