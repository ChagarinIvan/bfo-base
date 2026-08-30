<?php

declare(strict_types=1);

namespace App\Application\Dto\Auth;

use JsonSerializable;

final readonly class ViewTokenDto implements JsonSerializable
{
    public function __construct(public string $token, public string $tokenType)
    {
    }

    public function jsonSerialize(): array
    {
        return ['token' => $this->token, 'token_type' => $this->tokenType];
    }
}
