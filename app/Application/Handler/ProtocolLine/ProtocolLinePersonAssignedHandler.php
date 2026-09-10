<?php

declare(strict_types=1);

namespace App\Application\Handler\ProtocolLine;

use App\Application\Dto\Auth\UserId;
use App\Application\Service\Person\DisablePerson;
use App\Application\Service\Person\DisablePersonService;
use App\Application\Service\ProtocolLine\SetPersonToProtocolLines;
use App\Application\Service\ProtocolLine\SetPersonToProtocolLinesService;
use App\Domain\ProtocolLine\Event\ProtocolLinePersonAssigned;
use App\Domain\ProtocolLine\ProtocolLineRepository;
use App\Domain\Shared\Criteria;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;

final readonly class ProtocolLinePersonAssignedHandler implements ShouldQueueAfterCommit
{
    public function __construct(
        private SetPersonToProtocolLinesService $service,
        private ProtocolLineRepository $lines,
        private DisablePersonService $persons,
    ) {
    }

    public function handle(ProtocolLinePersonAssigned $event): void
    {
        $userId = new UserId($event->impression->by);

        $this->service->execute(new SetPersonToProtocolLines(
            $event->protocolLine->prepared_line,
            $event->protocolLine->person_id,
            $userId,
        ));

        if ($event->oldPersonId !== null && $this->lines->byCriteria(new Criteria(['personId' => $event->oldPersonId]))->isEmpty()) {
            $this->persons->execute(new DisablePerson((string) $event->oldPersonId, $userId));
        }
    }
}
