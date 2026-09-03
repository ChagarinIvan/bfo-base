<?php

declare(strict_types=1);

namespace App\Application\Dto\Person;

use App\Application\Dto\Auth\ImpressionDto;
use App\Application\Dto\Serialization\Groups;

final readonly class ViewPersonDto
{
    public function __construct(
        public string $id,
        public string $lastname,
        public string $firstname,
        public ?string $birthday,
        public int $rankId,
        public ?string $clubId,
        #[Groups(['authenticated'])]
        public ImpressionDto $created,
        #[Groups(['authenticated'])]
        public ImpressionDto $updated,
    ) {
    }
}
