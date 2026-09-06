<?php

declare(strict_types=1);

namespace App\Application\Service\Person;

use App\Application\Dto\ProtocolLine\ProtocolLineAssembler;
use App\Application\Dto\ProtocolLine\ViewProtocolLineDto;
use App\Application\Service\Person\Exception\ProtocolLineNotFound;
use App\Domain\Auth\Impression;
use App\Domain\ProtocolLine\ProtocolLineRepository;
use App\Domain\Shared\Clock;
use App\Domain\Shared\TransactionManager;

final readonly class ActivatePersonRankService
{
    public function __construct(
        private ProtocolLineRepository $protocolLines,
        private Clock $clock,
        private TransactionManager $transaction,
        private ProtocolLineAssembler $assembler,
    ) {
    }

    public function execute(ActivatePersonRank $command): ViewProtocolLineDto
    {
        return $this->transaction->run(function () use ($command): ViewProtocolLineDto {
            $line = $this->protocolLines->lockById($command->protocolLine()) ?? throw new ProtocolLineNotFound();
            $line->activateRank($command->date(), new Impression($this->clock->now(), $command->userId()));
            $this->protocolLines->update($line);

            return $this->assembler->toViewProtocolLineDto($line);
        });
    }
}
