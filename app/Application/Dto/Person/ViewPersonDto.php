<?php

declare(strict_types=1);

namespace App\Application\Dto\Person;

final readonly class ViewPersonDto
{
    public function __construct(
        public int $id,
        public string $lastname,
        public string $firstname,
        public ?string $birthday,
        public ?int $clubId,
        public int $eventsCount,
    ) {
    }
}
