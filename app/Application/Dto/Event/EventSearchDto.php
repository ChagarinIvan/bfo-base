<?php

declare(strict_types=1);

namespace App\Application\Dto\Event;

use App\Application\Dto\AbstractDto;

final class EventSearchDto extends AbstractDto
{
    public static function validationRules(): array
    {
        return [
            'competitionId' => 'numeric',
            'year' => 'numeric|digits:4',
            'idNotIn' => 'array|each:numeric',
        ];
    }
    public function __construct(
        public ?string $competitionId = null,
        public ?string $year = null,
        public ?array $idNotIn = null,
    ) {
    }

    public function fromArray(array $data): AbstractDto
    {
        $this->setStringParam('competitionId', $data);
        $this->setStringParam('year', $data);
        $this->idNotIn = $data['idNotIn'] ?? null;

        return $this;
    }
}
