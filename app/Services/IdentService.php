<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PersonPrompt;

/**
 * Class IdentService
 *
 * @package App\Services
 */
class IdentService
{
    private const EDIT_MAP = [
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
        'наталья' => ['наташа'],
        'михаил' => ['миша'],
        'анна' => ['аня'],
        'елена' => ['лена'],
    ];

    public const SYMBOL_MAP = [
        'с' => ['c'],
        'а' => ['a'],
        'о' => ['o'],
        'у' => ['y'],
        'р' => ['p'],
        'х' => ['x'],
        'е' => ['e'],
    ];

    /**
     * @param string $protocolLine
     * @return int
     */
    public function identPerson(string $protocolLine): int
    {
        $personPrompts = PersonPrompt::wherePrompt($protocolLine)->get();
        if ($personPrompts->isNotEmpty()) {
            return $personPrompts->first()->person_id;
        }

        $personPrompts = PersonPrompt::getPersonsByLevenshtein($protocolLine, 5);

        if ($personPrompts->isNotEmpty()) {
            return $personPrompts->first()->person_id;
        }

        return 0;
    }

    public static function prepareLine(string $line): string
    {
        foreach (self::SYMBOL_MAP as $symbol => $analogs) {
            $line = str_replace($analogs, $symbol, $line);
        }
        foreach (self::EDIT_MAP as $name => $analogs) {
            $line = str_replace($analogs, $name, $line);
        }
        return $line;
    }
}
