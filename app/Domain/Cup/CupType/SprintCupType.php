<?php

declare(strict_types=1);

namespace App\Domain\Cup\CupType;

class SprintCupType extends EliteCupType
{
    public function getNameKey(): string
    {
        return 'app.cup.type.sprint';
    }
}
