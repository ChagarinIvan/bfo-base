<?php

declare(strict_types=1);

namespace App\Domain\Auth;

use App\Domain\Auth\Factory\UserFactory;
use function filter_var;

final readonly class UserRegistrationService
{
    public function __construct(
        private UserRepository $users,
        private UserFactory $factory,
        private PasswordGenerator $passwords,
        private PasswordHasher $hashes,
    ) {
    }

    /** @throws InvalidEmail */
    public function register(string $email): string
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidEmail();
        }

        $user = $this->users->byEmail($email) ?? $this->factory->create($email);
        $password = $this->passwords->generate();
        $user->password = $this->hashes->hash($password);
        $this->users->add($user);

        return $password;
    }
}
