<?php

namespace App\Models\Cups;

use App\Models\Cup;
use App\Models\Group;
use Illuminate\Support\Collection;

interface CupTypeInterface
{
    public function getId(): string;
    public function getName(): string;
    public function calculate(Cup $cup, Collection $cupEvents, Group $mainGroup): array;
    public function getProtocolLines(Cup $cup, Group $mainGroup): Collection;
}
