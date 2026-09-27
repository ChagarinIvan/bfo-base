<?php

declare(strict_types=1);

namespace App\Domain\Cup\CupEvent;

final readonly class CupEventUpdateInput
{
    public function __construct(public int $eventId, public float $points, public int $userId)
    {
    }
}
