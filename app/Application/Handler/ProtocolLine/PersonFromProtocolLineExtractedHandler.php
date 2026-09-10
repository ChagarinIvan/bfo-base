<?php

declare(strict_types=1);

namespace App\Application\Handler\ProtocolLine;

use App\Application\Dto\Auth\UserId;
use App\Application\Service\ProtocolLine\SetPersonToProtocolLines;
use App\Application\Service\ProtocolLine\SetPersonToProtocolLinesService;
use App\Domain\ProtocolLine\Event\PersonFromProtocolLineExtracted;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;

final readonly class PersonFromProtocolLineExtractedHandler implements ShouldQueueAfterCommit
{
    public function __construct(
        private SetPersonToProtocolLinesService $service,
    ) {
    }

    public function handle(PersonFromProtocolLineExtracted $event): void
    {
        $this->service->execute(new SetPersonToProtocolLines(
            $event->protocolLine->prepared_line,
            $event->person->id,
            new UserId($event->impression->by),
        ));
    }
}
