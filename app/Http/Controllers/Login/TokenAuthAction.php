<?php

declare(strict_types=1);

namespace App\Http\Controllers\Login;

use App\Http\Controllers\AbstractRedirectAction;
use App\Http\Controllers\Error\Show404ErrorAction;
use App\Mail\PasswordMail;
use App\Models\User;
use Illuminate\Contracts\Encryption\Encrypter;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Hashing\HashManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use \Illuminate\Validation\Factory as Validator;
use Illuminate\Support\Str;

class TokenAuthAction extends AbstractRedirectAction
{
    private Encrypter $encrypter;
    private Validator $validator;
    private HashManager $hashManager;

    public function __construct(
        Redirector $redirector,
        Encrypter $encrypter,
        Validator $validator,
        HashManager $hashManager,
        Mailer $mailer,
    ) {
        parent::__construct($redirector);
        $this->encrypter = $encrypter;
        $this->validator = $validator;
        $this->hashManager = $hashManager;
        $this->mailer = $mailer;
    }

    public function __invoke(string $token): RedirectResponse
    {
        $email = $this->encrypter->decrypt($token);
        if (!$this->validator->validate(['email' => $email], ['email' => 'required|email'])) {
            return $this->redirector->action(Show404ErrorAction::class);
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
