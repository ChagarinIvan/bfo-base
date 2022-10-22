<?php

namespace App\Application\Exception\Club;

use RuntimeException;

final class ClubNotFound extends RuntimeException
{
    public static function byId(int $id): self
    {
        return new self("Club with ID $id not found.");
    }
}
