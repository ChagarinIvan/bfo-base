<?php

declare(strict_types=1);

namespace App\Application\Dto\Event;

use App\Application\Dto\AbstractDto;
use function array_map;

final class SearchEventDto extends AbstractDto
{
    public static function requestValidationRules(): array
    {
        return [
            'competitionId' => 'nullable|integer|min:1|required_without_all:groupId,ids',
            'groupId' => 'nullable|integer|min:1|required_without_all:competitionId,ids',
            'ids' => 'nullable|array',
            'ids.*' => 'integer|min:1',
            'withCompetition' => 'nullable|boolean',
            'competitionName' => 'nullable|string|min:3|max:255',
            'year' => 'nullable|numeric|digits:4',
            'date' => 'nullable|date_format:Y-m-d',
            'notRelatedToCup' => 'nullable|numeric',
        ];
    }

    public function __construct(
        public ?string $competitionId = null,
        public ?string $groupId = null,
        /** @var list<string>|null */
        public ?array $ids = null,
        public ?string $withCompetition = null,
        public ?string $competitionName = null,
        public ?string $year = null,
        public ?string $date = null,
        public ?string $notRelatedToCup = null,
    ) {
    }

    public function fromArray(array $data): self
    {
        $this->setStringParam('competitionId', $data);
        $this->setStringParam('groupId', $data);
        $this->ids = isset($data['ids']) ? array_map(static fn (mixed $id): string => (string) $id, $data['ids']) : null;
        $this->setStringParam('withCompetition', $data);
        $this->setStringParam('competitionName', $data);
        $this->setStringParam('year', $data);
        $this->setStringParam('date', $data);
        $this->setStringParam('notRelatedToCup', $data);

        return $this;
    }
}
