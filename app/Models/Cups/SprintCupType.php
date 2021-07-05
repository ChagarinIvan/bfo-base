<?php

namespace App\Models\Cups;

class SprintCupType extends EliteCupType
{
    public function getId(): string
    {
        return CupType::SPRINT;
    }

    public function getName(): string
    {
        return 'Sprint';
    }
}
