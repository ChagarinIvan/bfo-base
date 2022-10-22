<?php

namespace App\Application\Service\Club;

final class ViewClub
{
    public function __construct(private readonly string $clubId)
    {}

    public function clubId(): int
    {
        return (int)$this->clubId;
    }
}
