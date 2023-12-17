<?php

namespace App\Models\Cups;

use App\Models\Group\CupGroup;
use App\Models\Group\GroupMale;

class ElkPathCup extends EliteCupType
{
    public const ELITE_MEN_GROUPS = ['Elite–Mужчыны'];
    public const ELITE_WOMEN_GROUPS = ['Elite–Жанчыны'];

    public function getId(): string
    {
        return CupType::ELK_PATH;
    }

    public function getNameKey(): string
    {
        return 'app.cup.type.elk_path';
    }

    protected function getGroupsMap(CupGroup $group): array
    {
        $map = [
            (new CupGroup(GroupMale::Man))->id() => static::ELITE_MEN_GROUPS,
            (new CupGroup(GroupMale::Woman))->id() => static::ELITE_WOMEN_GROUPS,
        ];

        return $map[$group->id()] ?? [];
    }

    protected function withPayments(): bool
    {
        return false;
    }
}
