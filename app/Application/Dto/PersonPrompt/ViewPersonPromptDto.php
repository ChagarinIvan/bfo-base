<?php

declare(strict_types=1);

namespace App\Application\Dto\PersonPrompt;

use App\Application\Dto\Auth\ImpressionDto;
use App\Application\Dto\Serialization\Groups;

final readonly class ViewPersonPromptDto
{
    public function __construct(
        public string $id,
        public string $personId,
        public string $prompt,
        public string $metaphone,
        #[Groups(['authenticated'])]
        public ImpressionDto $created,
        #[Groups(['authenticated'])]
        public ImpressionDto $updated,
    ) {
    }
}
