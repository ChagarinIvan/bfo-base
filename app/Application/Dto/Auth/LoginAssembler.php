<?php

declare(strict_types=1);

namespace App\Application\Dto\Auth;

use App\Domain\Auth\AccessToken;

final class LoginAssembler
{
    public static function toViewTokenDto(AccessToken $token): ViewTokenDto
    {
        return new ViewTokenDto($token->value, $token->type);
    }
}
