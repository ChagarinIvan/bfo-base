<?php

declare(strict_types=1);

namespace App\Domain\Cup\CupType;

use App\Domain\Cup\Cup;
use App\Domain\Cup\CupEvent\CupEvent;
use App\Domain\Cup\CupEvent\CupEventPoint;
use App\Domain\Cup\Group\CupGroup;
use Illuminate\Support\Collection;

interface CupTypeInterface
{
    public function getNameKey(): string;

    /** @return array<string, CupEventPoint[]> */
    public function calculateCup(Cup $cup, Collection $cupEvents, CupGroup $mainGroup): array;

    /** @return array<int|string, CupEventPoint> */
    public function calculateEvent(CupEvent $cupEvent, CupGroup $mainGroup): array;

    /** @return CupGroup[] */
    public function groups(): array;
}
