<?php

declare(strict_types=1);

namespace App\Application\Service\Event;

use App\Domain\Auth\Impression;
use App\Domain\Shared\Criteria;

final readonly class StartEventProtocolRankRebuild
{
    public function __construct(
        private int $eventProtocolId,
        private Impression $impression,
    ) {
    }

    public function eventProtocolId(): int
    {
        return $this->eventProtocolId;
    }

    public function impression(): Impression
    {
        return $this->impression;
    }

    public function unidentifiedLinesCriteria(): Criteria
    {
        return new Criteria([
            'eventProtocolId' => $this->eventProtocolId,
            'unidentified' => true,
        ]);
    }
}
