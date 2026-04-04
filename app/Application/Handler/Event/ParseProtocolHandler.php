<?php

declare(strict_types=1);

namespace App\Application\Handler\Event;

use App\Domain\Event\ProtocolStorage;
use App\Services\ParserService;
use App\Services\ProtocolLineIdentService;
use App\Services\ProtocolLineService;
use Exception;
use Illuminate\Support\Facades\Log;
use function sprintf;

abstract class ParseProtocolHandler
{
    public function __construct(
        protected readonly ProtocolStorage $storage,
        protected readonly ParserService $parser,
        protected readonly ProtocolLineService $protocolLineService,
        protected readonly ProtocolLineIdentService $identService,
    ) {
    }

    protected function parse(string $path, int $eventId): void
    {
        Log::info('Parse protocol by path ' . $path);

        try {
            $protocol = $this->storage->get($path);
            $lineList = $this->parser->parse($protocol);
            Log::info(sprintf('Parsed %d lines.', $lineList->count()));
            $lines = $this->protocolLineService->fillProtocolLines($eventId, $lineList);
            Log::info(sprintf('Filled %d lines.', $lines->count()));
            $this->identService->identPersons($lineList);
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
