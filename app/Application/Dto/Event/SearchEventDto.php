<?php

declare(strict_types=1);

namespace App\Application\Dto\Event;

use App\Application\Dto\AbstractDto;

final class SearchEventDto extends AbstractDto
{
    public static function requestValidationRules(): array
    {
        return [
            'competitionId' => 'nullable|integer|min:1|required_without:groupId',
            'groupId' => 'nullable|integer|min:1|required_without:competitionId',
            'withCompetition' => 'nullable|boolean',
            'competitionName' => 'nullable|string|min:3|max:255',
            'year' => 'nullable|numeric|digits:4',
            'date' => 'nullable|date_format:Y-m-d',
            'flagId' => 'nullable|numeric',
            'notRelatedToCup' => 'nullable|numeric',
        ];
    }

    public function __construct(
        public ?string $competitionId = null,
        public ?string $groupId = null,
        public ?string $withCompetition = null,
        public ?string $competitionName = null,
        public ?string $flagId = null,
        public ?string $year = null,
        public ?string $date = null,
        public ?string $notRelatedToCup = null,
    ) {
    }

    public function fromArray(array $data): self
    {
        $this->setStringParam('competitionId', $data);
        $this->setStringParam('groupId', $data);
        $this->setStringParam('withCompetition', $data);
        $this->setStringParam('competitionName', $data);
        $this->setStringParam('flagId', $data);
        $this->setStringParam('year', $data);
        $this->setStringParam('date', $data);
        $this->setStringParam('notRelatedToCup', $data);

        return $this;
    }
}
