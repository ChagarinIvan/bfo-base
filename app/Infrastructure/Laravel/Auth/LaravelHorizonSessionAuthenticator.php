<?php

declare(strict_types=1);

namespace App\Infrastructure\Laravel\Auth;

use App\Domain\Auth\HorizonSessionAuthenticator;
use App\Domain\Auth\UserRepository;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Contracts\Session\Session;
use LogicException;

final readonly class LaravelHorizonSessionAuthenticator implements HorizonSessionAuthenticator
{
    public function __construct(
        private AuthFactory $auth,
        private Session $session,
        private UserRepository $users,
    ) {
    }

    public function start(int $userId): void
    {
        $guard = $this->guard();
        $user = $this->users->byId($userId);
        if ($user === null) {
            throw new LogicException('The authenticated user no longer exists.');
        }

        $this->session->invalidate();
        $this->session->regenerateToken();
        $guard->login($user);
        $this->session->regenerate();
    }

    public function end(int $userId): void
    {
        $guard = $this->guard();
        if ((int) $guard->id() === $userId) {
            $guard->logout();
        }
        $this->session->invalidate();
        $this->session->regenerateToken();
    }

    private function guard(): StatefulGuard
    {
        $guard = $this->auth->guard('web');
        if (!$guard instanceof StatefulGuard) {
            throw new LogicException('The web guard must support sessions.');
        }

        return $guard;
    }
}
