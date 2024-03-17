<?php

declare(strict_types=1);

namespace App\Domain\Shared;
use function str_replace;

final class SymbolNormalizer
{
    public const SYMBOL_MAP = [
        'с' => ['c'],
        'а' => ['a'],
        'о' => ['o'],
        'у' => ['y'],
        'р' => ['p'],
        'х' => ['x'],
        'е' => ['e', 'ё'],
    ];

    public function normalize(string $value): string
    {
        foreach (self::SYMBOL_MAP as $symbol => $analogs) {
            $value = str_replace($analogs, $symbol, $value);
        }

        return $value;
    }
}
