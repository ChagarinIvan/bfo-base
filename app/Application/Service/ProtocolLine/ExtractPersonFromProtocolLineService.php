<?php

declare(strict_types=1);

namespace App\Application\Service\ProtocolLine;

use App\Application\Dto\Person\PersonAssembler;
use App\Application\Dto\Person\ViewPersonDto;
use App\Application\Service\Person\Exception\ProtocolLineNotFound;
use App\Domain\Auth\Impression;
use App\Domain\Person\PersonExtractor;
use App\Domain\Person\PersonRepository;
use App\Domain\ProtocolLine\ProtocolLineRepository;
use App\Domain\Shared\Clock;
use App\Domain\Shared\TransactionManager;

final readonly class ExtractPersonFromProtocolLineService
{
    public function __construct(
        private ProtocolLineRepository $protocolLines,
        private PersonRepository $persons,
        private PersonExtractor $extractor,
        private PersonAssembler $assembler,
        private Clock $clock,
        private TransactionManager $transaction,
    ) {
    }

    public function execute(ExtractPersonFromProtocolLine $command): ViewPersonDto
    {
        return $this->transaction->run(function () use ($command): ViewPersonDto {
            $protocolLine = $this->protocolLines->lockById($command->protocolLineId()) ?? throw new ProtocolLineNotFound();
            $person = $protocolLine->extractPerson($this->extractor, new Impression($this->clock->now(), $command->userId()->id));
            $this->persons->add($person);
            $this->protocolLines->update($protocolLine);

            return $this->assembler->toViewPersonDto($person);
        });
    }
}
