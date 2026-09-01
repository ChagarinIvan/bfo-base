<?php

declare(strict_types=1);

namespace App\Application\Service\Person;

use App\Application\Port\PersonRankSourceReader;
use App\Application\Service\Person\Exception\PersonRankNotFound;
use App\Application\Service\Person\Exception\ProtocolLineNotFound;
use App\Application\Service\Rank\RebuildPersonRanks;
use App\Application\Service\Rank\RebuildPersonRanksService;
use App\Domain\ProtocolLine\ProtocolLineRepository;
use App\Domain\Shared\TransactionManager;

final readonly class ActivatePersonRankService
{
    public function __construct(
        private PersonRankSourceReader $sources,
        private ProtocolLineRepository $protocolLines,
        private RebuildPersonRanksService $rebuild,
        private TransactionManager $transaction,
    ) {
    }

    public function execute(ActivatePersonRank $command): int
    {
        $source = $this->sources->byHistoryId($command->id()) ?? throw new PersonRankNotFound();

        $this->transaction->run(function () use ($source, $command): void {
            $line = $this->protocolLines->lockById($source->protocolLineId) ?? throw new ProtocolLineNotFound();
            $line->activateRank($command->date());
            $this->protocolLines->update($line);
        });

        $this->rebuild->execute(new RebuildPersonRanks($source->personId));

        return $source->personId;
    }
}
