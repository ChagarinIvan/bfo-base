<?php

declare(strict_types=1);

namespace App\Application\Service\ProtocolLine;

use App\Application\Service\Person\Exception\ProtocolLineNotFound;
use App\Domain\Auth\Impression;
use App\Domain\ProtocolLine\ProtocolLineRepository;
use App\Domain\Shared\Clock;
use App\Domain\Shared\TransactionManager;

final readonly class AssignPersonToProtocolLineService
{
    public function __construct(
        private ProtocolLineRepository $protocolLines,
        private Clock $clock,
        private TransactionManager $transaction,
    ) {
    }

    public function execute(AssignPersonToProtocolLine $command): void
    {
        $this->transaction->run(function () use ($command): void {
            $protocolLine = $this->protocolLines->lockById($command->protocolLineId()) ?? throw new ProtocolLineNotFound();
            $protocolLine->assignPerson($command->personId(), new Impression($this->clock->now(), $command->userId()->id));
            $this->protocolLines->update($protocolLine);
        });
    }
}
