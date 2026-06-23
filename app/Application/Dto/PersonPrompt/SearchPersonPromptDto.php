<?php

declare(strict_types=1);

namespace App\Application\Dto\PersonPrompt;

use App\Application\Dto\AbstractDto;

final class SearchPersonPromptDto extends AbstractDto
{
    public static function parametersValidationRules(): array
    {
        return [
            'personId' => 'numeric',
            'activePerson' => 'bool',
        ];
    }

    public function __construct(
        public ?string $personId = null,
        public bool $activePerson = true,
    ) {
    }

    public function fromArray(array $data): self
    {
        $this->setStringParam('personId', $data);

        return $this;
    }
}
