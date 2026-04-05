<?php

declare(strict_types=1);

namespace App\Application\Dto\PersonPrompt;

use App\Application\Dto\AbstractDto;

final class PersonPromptDto extends AbstractDto
{
    public string $prompt;

    public static function requestValidationRules(): array
    {
        return [
            'prompt' => 'required|max:255',
        ];
    }

    public function fromArray(array $data): self
    {
        $this->prompt = $data['prompt'];

        return $this;
    }
}
