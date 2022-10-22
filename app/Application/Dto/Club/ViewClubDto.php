<?php

namespace App\Application\Dto\Club;

final class ViewClubDto
{
    public function __construct(
        public readonly string $id,
        public readonly string $name
    ) {}
}
