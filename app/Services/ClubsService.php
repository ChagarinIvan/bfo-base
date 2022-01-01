<?php

namespace App\Services;

use App\Models\Club;
use Illuminate\Support\Collection;

class ClubsService
{
    private const EDIT_MAP = [
        'ко ' => ['ка ', 'oc '],
        'ксо ' => ['кса '],
        'бгу' => ['бду', 'bsu'],
    ];

    /**
     * @return Collection|Club[]
     */
    public function getAllClubs(): Collection
    {
        return Club::all();
    }

    public static function normalizeName(string $clubName): string
    {
        $clubName = mb_strtolower($clubName);
        $clubName = str_replace(['\'', '"', '«', '»'], '', $clubName);
        $clubName = preg_replace('#\s+#', ' ', $clubName);
        //Исправляем символы
        foreach (ProtocolLineIdentService::SYMBOL_MAP as $symbol => $analogs) {
            $clubName = str_replace($analogs, $symbol, $clubName);
        }

        foreach (self::EDIT_MAP as $name => $analogs) {
            $clubName = str_replace($analogs, $name, $clubName);
        }

        return $clubName;
    }
}
