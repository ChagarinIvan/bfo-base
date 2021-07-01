<?php

namespace App\Models\Cups;

use App\Models\Cup;
use Illuminate\Support\Collection;

interface CupTypeInterface
{
    public function getId(): string;
    public function getName(): string;
    public function calculate(Cup $cup, Collection $events, Collection $protocolLines): array;
}
