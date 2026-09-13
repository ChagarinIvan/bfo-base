<?php

declare(strict_types=1);

namespace App\Application\Service\Event;

use App\Domain\Event\EventProtocol;
use App\Domain\Event\EventProtocolRepository;
use App\Domain\Event\EventRepository;
use App\Domain\Event\ProtocolStorage;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Services\ParserService;
use App\Services\ProtocolLineIdentService;
use App\Services\ProtocolLineService;
use Exception;

final readonly class ParseEventProtocolService
{
    public function __construct(
        private ProtocolStorage $storage,
        private ParserService $parser,
        private ProtocolLineService $protocolLines,
        private ProtocolLineIdentService $identification,
        private EventProtocolRepository $protocolRuns,
        private EventRepository $events,
        private StartEventProtocolRankRebuildService $rankRebuild,
    ) {
    }

    public function execute(ParseEventProtocol $command): void
    {
        $run = $this->protocolRuns->byId($command->eventProtocolId());

        $event = $this->events->byId($command->eventId());

        if (!$run instanceof EventProtocol || $event === null || $run->event_id !== $event->id || $event->active_event_protocol_id !== $run->id) {
            return;
        }

        $parsingStarted = $run->startParsing($command->impression());
        $this->protocolRuns->update($run);

        if (! $parsingStarted) {
            return;
        }

        try {
            $lines = $this->protocolLines->fillProtocolLines(
                $command->eventId(),
                $this->parser->parse($this->storage->get($command->path())),
                $run->id,
            );

            $run->startIdentifying($lines->count(), $command->impression());

            $this->identification->identPersons($lines, $command->impression());

            $lines->each(static function (ProtocolLine $line) use ($run, $command): void {
                $line->refresh();

                if ($line->person_id === null) {
                    return;
                }

                $run->recordIdentifiedLine($line->id, $command->impression());
            });

            $this->rankRebuild->execute(new StartEventProtocolRankRebuild($run->id, $command->impression()));
        } catch (Exception) {
            $run->fail($command->impression());
        }

        $this->protocolRuns->update($run);
    }
}
