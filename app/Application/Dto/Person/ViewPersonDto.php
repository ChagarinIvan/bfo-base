<?php

declare(strict_types=1);

namespace App\Application\Dto\Person;

use App\Application\Dto\Auth\ImpressionDto;

final readonly class ViewPersonDto
{
    public function __construct(
        public string $id,
        public string $lastname,
        public string $firstname,
        public ?int $birthYear,
        public ImpressionDto $created,
        public ImpressionDto $updated,
    ) {
    }
}
