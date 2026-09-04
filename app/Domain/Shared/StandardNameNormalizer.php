<?php

declare(strict_types=1);

namespace App\Domain\Shared;

use function in_array;
use function str_replace;

final readonly class StandardNameNormalizer implements NameNormalizer
{
    /** @var array<string, list<string>> */
    private const array SYMBOL_MAP = [
        'с' => ['c'],
        'а' => ['a'],
        'о' => ['o'],
        'у' => ['y'],
        'р' => ['p'],
        'х' => ['x'],
        'е' => ['e', 'ё'],
    ];

    /** @var array<string, list<string>> */
    private const array EDIT_MAP = [
        'дмитрий' => ['дима'],
        'павел' => ['паша'],
        'мария' => ['маша'],
        'иван' => ['ваня'],
        'татьяна' => ['таня'],
        'анастасия' => ['настя'],
        'екатерина' => ['катя'],
        'юрий' => ['юра'],
        'ольга' => ['оля'],
        'валентина' => ['валя'],
        'александр' => ['саша'],
        'алексей' => ['леша'],
        'светлана' => ['света'],
        'владислав' => ['влад'],
        'вячеслав' => ['слава'],
        'наталья' => ['наташа'],
        'михаил' => ['миша'],
        'анна' => ['аня'],
        'елена' => ['лена'],
    ];

    public function normalize(string $name): string
    {
        foreach (self::SYMBOL_MAP as $symbol => $analogs) {
            $name = str_replace($analogs, $symbol, $name);
        }

        foreach (self::EDIT_MAP as $normalized => $analogs) {
            if (in_array($name, $analogs, true)) {
                return $normalized;
            }
        }

        return $name;
    }
}
