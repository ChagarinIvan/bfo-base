<?php

namespace App\Models\Cups;

use App\Models\Cup;
use App\Models\CupEvent;
use App\Models\Group;
use Illuminate\Support\Collection;

interface CupTypeInterface
{
    public function getId(): string;
    public function getNameKey(): string;
    public function calculateCup(Cup $cup, Collection $cupEvents, Group $mainGroup): array;
    public function calculateEvent(CupEvent $cupEvent, Group $mainGroup): Collection;
    public function getCupEventParticipatesCount(CupEvent $cupEvent): int;

    /**
     * @return Collection|Group[]
     */
    public function getGroups(): Collection;
}
