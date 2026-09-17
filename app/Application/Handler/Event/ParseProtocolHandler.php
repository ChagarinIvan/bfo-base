<?php

declare(strict_types=1);

namespace App\Application\Handler\Event;

use App\Domain\Auth\Impression;
use App\Domain\Event\Protocol;
use App\Domain\Shared\Storage;
use App\Services\ParserService;
use App\Services\ProtocolLineIdentService;
use App\Services\ProtocolLineService;
use Exception;
use Illuminate\Support\Facades\Log;
use function array_pop;
use function explode;
use function sprintf;

abstract class ParseProtocolHandler
{
    public function __construct(
        protected readonly Storage $storage,
        protected readonly ParserService $parser,
        protected readonly ProtocolLineService $protocolLineService,
        protected readonly ProtocolLineIdentService $identService,
    ) {
    }

    protected function parse(string $path, int $eventId, Impression $impression): void
    {
        if ($path === '') {
            return;
        }

        Log::info('Parse protocol by path ' . $path);

        try {
            $data = explode('@@', $path);
            $extension = array_pop($data) ?: '';
            $protocol = new Protocol($this->storage->get($path), $extension);
            $lineList = $this->parser->parse($protocol);
            Log::info(sprintf('Parsed %d lines.', $lineList->count()));
            $lines = $this->protocolLineService->fillProtocolLines($eventId, $lineList);
            Log::info(sprintf('Filled %d lines.', $lines->count()));
            $this->identService->identPersons($lines, $impression);
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }
}
