<?php

declare(strict_types=1);

namespace App\Models\Cups;

use App\Models\Cup;
use App\Models\CupEvent;
use App\Models\CupEventPoint;
use App\Models\Group\CupGroup;
use Illuminate\Support\Collection;

interface CupTypeInterface
{
    public function getId(): string;

    public function getNameKey(): string;

    /** @return array<string, CupEventPoint[]> */
    public function calculateCup(Cup $cup, Collection $cupEvents, CupGroup $mainGroup): array;

    public function calculateEvent(CupEvent $cupEvent, CupGroup $mainGroup): Collection;

    public function getCupEventParticipatesCount(CupEvent $cupEvent): int;

    /**
     * @return Collection|CupGroup[]
     */
    public function getGroups(): Collection|array;
}
