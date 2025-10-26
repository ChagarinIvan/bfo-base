<?php

declare(strict_types=1);

namespace App\Application\Dto\Event;

use App\Application\Dto\AbstractDto;

final class EventSearchDto extends AbstractDto
{
    public static function requestValidationRules(): array
    {
        return [
            'competitionId' => 'numeric',
            'year' => 'numeric|digits:4',
            'flagId' => 'numeric',
            'notRelatedToCup' => 'numeric',
        ];
    }
    public function __construct(
        public ?string $competitionId = null,
        public ?string $flagId = null,
        public ?string $year = null,
        public ?string $notRelatedToCup = null,
    ) {
    }

    public function fromArray(array $data): AbstractDto
    {
        $this->setStringParam('competitionId', $data);
        $this->setStringParam('flagId', $data);
        $this->setStringParam('year', $data);
        $this->notRelatedToCup = $data['notRelatedToCup'] ?? null;

        return $this;
    }
}
