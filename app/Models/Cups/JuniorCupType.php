<?php

namespace App\Models\Cups;

use App\Models\Group;
use Illuminate\Support\Collection;

class JuniorCupType extends EliteCupType
{
    public function getId(): string
    {
        return CupType::JUNIORS;
    }

    public function getNameKey(): string
    {
        return 'app.cup.type.junior';
    }

    public function getGroups(): Collection
    {
        return $this->groupsService->getGroups([Group::M20, Group::W20]);
    }
}
