<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Mail\PasswordMail;
use App\Mail\RegistrationUrlMail;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    public function login(): View
    {
        return view('auth.login');
    }

    public function signIn(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();
        return redirect('competitions/y0');
    }

    public function registration(Request $request): View|RedirectResponse
    {
        if ($request->method() === 'POST') {
            $form = $request->validate([
                'email' => 'required|email',
            ]);

            $email = $form['email'];
            $token = Crypt::encrypt($email);
            Mail::send(new RegistrationUrlMail($email, Url::make("/login/auth/{$token}")));
            return redirect('competitions/y0');
        }

        return view('auth.registration');
    }

    public function auth(string $token): View
    {
        $email = Crypt::decrypt($token);
        if (!Validator::validate(['email' => $email], ['email' => 'required|email'])) {
            return view('errors.404');
        }
        $users = User::whereEmail($email)->get();
        if ($users->isEmpty()) {
            $user = new User();
            $user->email = $email;
        } else {
            $user = $users->first();
        }

        $password = Str::random(8);
        $user->password = Hash::make($password);
        $user->save();
        Mail::send(new PasswordMail($email, $password));

        return view('auth.registration');
    }
}
