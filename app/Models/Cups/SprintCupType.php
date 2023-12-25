<?php

declare(strict_types=1);

namespace App\Models\Cups;

class SprintCupType extends EliteCupType
{
    public function getId(): string
    {
        return CupType::SPRINT;
    }

    public function getNameKey(): string
    {
        return 'app.cup.type.sprint';
    }
}
