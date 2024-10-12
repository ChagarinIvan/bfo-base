<?php

declare(strict_types=1);

namespace App\Application\Handler\Rank;

use App\Domain\ProtocolLine\Event\ProtocolLineRankActivated;
use App\Services\RankService;

final readonly class ProtocolLineRankActivatedHandler
{
    public function __construct(private RankService $service)
    {
    }

    public function handle(ProtocolLineRankActivated $event): void
    {
        $this->service->reFillRanksForPerson($event->protocolLine->person_id);
    }
}
