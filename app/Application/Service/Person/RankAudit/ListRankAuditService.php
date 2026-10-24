<?php

declare(strict_types=1);

namespace App\Application\Service\Person\RankAudit;

use App\Application\Dto\Person\RankAudit\RankAuditAssembler;
use App\Application\Dto\Person\RankAudit\RankAuditDto;
use App\Application\Service\Person\Exception\PersonNotFound;
use App\Domain\Person\Audit\RankAuditRepository;
use App\Domain\Person\PersonRepository;
use App\Domain\Shared\Pagination\Slice;

final readonly class ListRankAuditService
{
    public function __construct(private PersonRepository $persons, private RankAuditRepository $audit, private RankAuditAssembler $assembler)
    {
    }

    /** @return Slice<RankAuditDto> */
    public function execute(ListRankAudit $command): Slice
    {
        $this->persons->byId($command->personId) ?? throw new PersonNotFound();

        return $this->audit->byCriteria($command->criteria())->map($this->assembler->toDto(...));
    }
}
