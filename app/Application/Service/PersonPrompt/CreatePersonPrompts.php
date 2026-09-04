<?php

declare(strict_types=1);

namespace App\Application\Service\PersonPrompt;

use App\Application\Dto\Auth\UserId;

final readonly class CreatePersonPrompts
{
    public function __construct(
        private int $personId,
        private string $firstname,
        private string $lastname,
        private ?string $birthdayYear,
        private UserId $userId,
    ) {
    }

    public function personId(): int
    {
        return $this->personId;
    }

    public function firstname(): string
    {
        return $this->firstname;
    }

    public function lastname(): string
    {
        return $this->lastname;
    }

    public function birthdayYear(): ?string
    {
        return $this->birthdayYear;
    }

    public function userId(): int
    {
        return $this->userId->id;
    }
}
