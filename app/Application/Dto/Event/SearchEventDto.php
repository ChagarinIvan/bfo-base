<?php

declare(strict_types=1);

namespace App\Application\Dto\Event;

use App\Application\Dto\AbstractDto;

final class SearchEventDto extends AbstractDto
{
    public static function requestValidationRules(): array
    {
        return [
            'competitionId' => 'required|integer|min:1',
            'year' => 'nullable|numeric|digits:4',
            'flagId' => 'nullable|numeric',
            'notRelatedToCup' => 'nullable|numeric',
        ];
    }

    public function __construct(
        public ?string $competitionId = null,
        public ?string $flagId = null,
        public ?string $year = null,
        public ?string $notRelatedToCup = null,
    ) {
    }

    public function fromArray(array $data): self
    {
        $this->setStringParam('competitionId', $data);
        $this->setStringParam('flagId', $data);
        $this->setStringParam('year', $data);
        $this->setStringParam('notRelatedToCup', $data);

        return $this;
    }
}
