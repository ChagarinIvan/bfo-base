<?php

declare(strict_types=1);

namespace App\Application\Handler\Event;

use App\Domain\Event\Event\EventCreated;
use App\Domain\Event\ProtocolStorage;
use App\Services\ParserService;
use App\Services\ProtocolLineService;

final readonly class ParseProtocolHandler
{
    public function __construct(
        private ProtocolStorage $storage,
        private ParserService $parser,
        private ProtocolLineService $protocolLineService,
    ) {
    }

    public function handle(EventCreated $systemEvent): void
    {
        $protocol = $systemEvent->event->protocol($this->storage);
        $lineList = $this->parser->parse($protocol);
        $this->protocolLineService->fillProtocolLines($systemEvent->event->id, $lineList);
    }
}
