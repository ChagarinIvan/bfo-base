<?php

declare(strict_types=1);

namespace App\Application\Handler\Event;

use App\Domain\Event\Event\EventCreated;
use App\Domain\Event\ProtocolStorage;
use App\Services\ParserService;
use App\Services\ProtocolLineIdentService;
use App\Services\ProtocolLineService;

 abstract readonly class ParseProtocolHandler
{
    public function __construct(
        private ProtocolStorage $storage,
        private ParserService $parser,
        private ProtocolLineService $protocolLineService,
        private ProtocolLineIdentService $identService,
    ) {
    }

    protected function parse(string $path, int $eventId): void
    {
        $protocol = $this->storage->get($path);
        $lineList = $this->parser->parse($protocol);
        $this->protocolLineService->fillProtocolLines($eventId, $lineList);
        $this->identService->identPersons($lineList);
    }
}
