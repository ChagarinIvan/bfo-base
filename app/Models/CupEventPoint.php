<?php

namespace App\Models;

/**
 * Class CupEventPoint
 */
class CupEventPoint
{
    public function __construct(
        public int $eventCupId,
        public int $protocolLineId,
        public int $points,
    ) {}
}
