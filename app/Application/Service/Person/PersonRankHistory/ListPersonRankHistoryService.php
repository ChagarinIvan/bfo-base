<?php

declare(strict_types=1);

namespace App\Application\Service\Person\PersonRankHistory;

use App\Application\Dto\Person\PersonAssembler;
use App\Application\Dto\Person\ViewPersonRankHistoryDto;
use App\Domain\Person\PersonRepository;

final readonly class ListPersonRankHistoryService
{
    public function __construct(
        private PersonRepository $persons,
        private PersonAssembler $assembler,
    ) {
    }

    /** @return list<ViewPersonRankHistoryDto> */
    public function execute(ListPersonRankHistory $command): array
    {
        $person = $this->persons->byId($command->personId());

        if ($person === null) {
            return [];
        }

        return $person->rankHistories
            ->map($this->assembler->toViewPersonRankHistoryDto(...))
            ->all()
        ;
    }
}
