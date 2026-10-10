<?php

declare(strict_types=1);

namespace App\Application\Service\Person;

use App\Domain\Person\PersonRankExportRow;
use App\Domain\Person\PersonRepository;

final readonly class ExportPersonRanksService
{
    public function __construct(private PersonRepository $persons)
    {
    }

    /** @return iterable<PersonRankExportRow> */
    public function execute(ExportPersonRanks $command): iterable
    {
        return $this->persons->exportByCriteria($command->criteria());
    }
}
