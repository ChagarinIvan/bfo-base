<?php

namespace App\Models\Cups;

class BikeCupType extends EliteCupType
{
    public function getId(): string
    {
        return CupType::BIKE;
    }

    public function getName(): string
    {
        return 'Вело';
    }
}
