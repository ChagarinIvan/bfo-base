<?php

declare(strict_types=1);

namespace App\Application\Handler\Rank;

use App\Application\Service\Rank\RebuildPersonRanks;
use App\Application\Service\Rank\RebuildPersonRanksService;
use App\Domain\ProtocolLine\Event\ProtocolLineRankActivated;
use Illuminate\Contracts\Queue\ShouldQueue;

final readonly class ProtocolLineRankActivatedHandler implements ShouldQueue
{
    public function __construct(private RebuildPersonRanksService $service)
    {
    }

    public function handle(ProtocolLineRankActivated $event): void
    {
        if ($event->protocolLine->person_id) {
            $this->service->execute(new RebuildPersonRanks($event->protocolLine->person_id));
        }
    }
}
