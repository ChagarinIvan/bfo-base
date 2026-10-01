<?php

declare(strict_types=1);

namespace App\Application\Service\Event;

use App\Application\Service\Event\Exception\EventNotFound;
use App\Domain\Auth\Impression;
use App\Domain\Event\EventRepository;
use App\Domain\Event\ProtocolParser;
use App\Domain\ProtocolLine\Factory\ProtocolLinesFactory;
use App\Domain\ProtocolLine\ProtocolLineRepository;
use App\Domain\Shared\Clock;
use App\Domain\Shared\TransactionManager;

final readonly class ParseEventProtocolService
{
    public function __construct(
        private ProtocolParser $parser,
        private ProtocolLinesFactory $protocolLinesFactory,
        private ProtocolLineRepository $protocolLines,
        private EventRepository $events,
        private TransactionManager $transactional,
        private Clock $clock,
    ) {
    }

    public function execute(ParseEventProtocol $command): void
    {
        $this->transactional->run(function () use ($command): void {
            $event = $this->events->lockById($command->eventId) ?? throw new EventNotFound();

            $lines = $event->parse(
                $command->processingToken,
                $this->parser,
                $this->protocolLinesFactory,
                new Impression($this->clock->now(), $command->userId),
            );

            $this->protocolLines->add(...$lines);
            $this->events->update($event);
        });
    }
}
