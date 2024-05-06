<?php

declare(strict_types=1);

namespace App\Domain\Cup\CupType;

use App\Domain\Cup\Cup;
use App\Domain\Cup\Group\CupGroup;
use App\Domain\CupEvent\CupEvent;
use App\Models\CupEventPoint;
use Illuminate\Support\Collection;

interface CupTypeInterface
{
    public function getNameKey(): string;

    /** @return array<string, CupEventPoint[]> */
    public function calculateCup(Cup $cup, Collection $cupEvents, CupGroup $mainGroup): array;

    public function calculateEvent(CupEvent $cupEvent, CupGroup $mainGroup): Collection;

    /**
     * @param array<int, CupGroup> $groups
     */
    public function getCupEventParticipatesCount(CupEvent $cupEvent, ?array $groups = null): int;

    /**
     * @return Collection|CupGroup[]
     */
    public function getGroups(): Collection|array;
}
