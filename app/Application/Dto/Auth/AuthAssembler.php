<?php

declare(strict_types=1);

namespace App\Application\Dto\Auth;

use App\Domain\Auth\Impression;
use DateTimeImmutable;

final class AuthAssembler
{
    public function toImpressionDto(Impression $impression): ImpressionDto
    {
        return new ImpressionDto(
            $impression->at->format(DateTimeImmutable::ATOM),
            (string) $impression->by,
        );
    }
}
