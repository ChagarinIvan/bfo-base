<?php

declare(strict_types=1);

namespace App\Application\Dto\Auth;

use JsonSerializable;

final readonly class UserDto implements JsonSerializable
{
    public function __construct(public int $id, public ?string $name, public string $email)
    {
    }

    public function jsonSerialize(): array
    {
        return ['id' => $this->id, 'name' => $this->name, 'email' => $this->email];
    }
}
