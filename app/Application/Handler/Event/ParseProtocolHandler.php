<?php

declare(strict_types=1);

namespace App\Application\Handler\Event;

use App\Domain\Event\Event\EventCreated;
use App\Domain\Event\ProtocolStorage;
use App\Services\ParserService;
use App\Services\ProtocolLineIdentService;
use App\Services\ProtocolLineService;
use JetBrains\PhpStorm\NoReturn;

final readonly class ParseProtocolHandler
{
    public function __construct(
        private ProtocolStorage $storage,
        private ParserService $parser,
        private ProtocolLineService $protocolLineService,
        private ProtocolLineIdentService $identService,
    ) {
    }

    #[NoReturn]
    public function handle(EventCreated $systemEvent): void
    {
        $protocol = $systemEvent->event->protocol($this->storage);
        dump($protocol);
        $lineList = $this->parser->parse($protocol);
        dd($lineList);
        $this->protocolLineService->fillProtocolLines($systemEvent->event->id, $lineList);
        $this->identService->identPersons($lineList);
    }
}
