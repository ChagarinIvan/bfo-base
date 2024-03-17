<?php

declare(strict_types=1);

namespace App\Application\Dto\PersonPrompt;

use App\Application\Dto\Auth\ImpressionDto;

final readonly class ViewPersonPromptDto
{
    public function __construct(
        public string $id,
        public string $personId,
        public string $prompt,
        public string $metaphone,
        public ImpressionDto $created,
        public ImpressionDto $updated,
    ) {
    }
}
