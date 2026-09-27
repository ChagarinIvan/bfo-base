<?php

declare(strict_types=1);

namespace App\Domain\Cup\CupEvent\Factory;

final readonly class CupEventInput
{
    public function __construct(public int $cupId, public int $eventId, public float $points, public int $userId)
    {
    }
}
