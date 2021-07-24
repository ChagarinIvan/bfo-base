<?php

namespace App\Models\Cups;

use App\Models\Cup;
use App\Models\Group;
use App\Models\Person;
use App\Models\ProtocolLine;
use Illuminate\Support\Collection;

class EliteCupType extends MasterCupType
{
    public function getId(): string
    {
        return CupType::ELITE;
    }

    public function getName(): string
    {
        return 'Elite';
    }

    public function getProtocolLines(Cup $cup, Group $mainGroup): Collection
    {
        $startYear = $cup->year - $mainGroup->years();
        $finishYear = $startYear - 5;

        $persons = Person::where('birthday', '<=', "{$startYear}-01-01")
            ->where('birthday', '>', "{$finishYear}-01-01")
            ->get();

        return ProtocolLine::with('person')
            ->whereIn('event_id', $cup->events->pluck('event_id'))
            ->whereIn('person_id', $persons->pluck('id'))
            ->get();
    }
}
