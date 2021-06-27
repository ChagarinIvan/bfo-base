<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class IdentService
 *
 * @package App\Services
 */
class IdentService
{
    /**
     * карта исправления имён, разные сокращения и формы аналоги
     */
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

    /**
     * карта исправления символов, например случайно поставленные английские символы совпадающие по написанию с русскими
     */
    public const SYMBOL_MAP = [
        'с' => ['c'],
        'а' => ['a'],
        'о' => ['o'],
        'у' => ['y'],
        'р' => ['p'],
        'х' => ['x'],
        'е' => ['e', 'ё'],
    ];

//    private Collection|array $persons;

    public function __construct()
    {
//        $this->persons = Person::all();
    }

    /**
     * Идентификация прямым запросом в базу на поиск литий протокола с такой же "идентификационной" строкой и имеющимся person_id.
     * На вход коллекция линий протокола, на выходе число апдейтнутых строк.
     * Используется при создании или редактировании протокола соревнований для быстрой идентификации части людей.
     *
     * @param Collection $protocolLines
     * @return int
     */
    public function simpleIdent(Collection $protocolLines): int
    {
        return DB::table('protocol_lines', 'pls')
            ->join('protocol_lines AS plj', 'plj.prepared_line', '=', 'pls.prepared_line')
            ->whereNull('pls.person_id')
            ->whereNotNull('plj.person_id')
            ->whereIn('pls.id', $protocolLines->pluck('id'))
            ->update(['pls.person_id' => DB::raw('plj.person_id')]);
    }

    /**
     * @param string $searchLine
     * @return int
     */
    public function identPerson(string $searchLine): int
    {
        return 0;
//        $result = new Collection();
//
//        foreach ($this->persons as $person) {
//            $personData = [
//                $person->lastname,
//                $person->firstname,
//            ];
//            $personLine = mb_strtolower(implode('_', $personData));
//            $personLine = self::prepareLine($personLine);
//
//            $rank = levenshtein($searchLine, $personLine);
//            $result->push([
//                'id' => $person->id,
//                'rank' => $rank,
//            ]);
//
//            if ($person->birthday !== null) {
//                $personData[] = $person->birthday->format('Y');
//                $personLine = mb_strtolower(implode('_', $personData));
//                $personLine = self::prepareLine($personLine);
//
//                $rank = levenshtein($searchLine, $personLine);
//                $result->push([
//                    'id' => $person->id,
//                    'rank' => $rank,
//                ]);
//            }
//
//            foreach ($person->getPrompts() as $prompt) {
//                $prompt = self::prepareLine($prompt);
//                $rank = levenshtein($searchLine, $prompt);
//                $result->push([
//                    'id' => $person->id,
//                    'rank' => $rank,
//                ]);
//            }
//        }
//
//        $result = $result->groupBy('rank');
//        $result = $result->toArray();
//        ksort($result);
//        $minRank = array_key_first($result);
//        if ($minRank <= 5) {
//            $result = reset($result);
//            $result = reset($result);
//            return $result['id'];
//        }
//
//        return 0;
    }

    /**
     * Процесс нормализации фамилии имени (везде идёт замена неверных символов, заменяются формы имени)
     *
     * @param string $line
     * @return string
     */
    public static function prepareLine(string $line): string
    {
        //Исправляем символы
        foreach (self::SYMBOL_MAP as $symbol => $analogs) {
            $line = str_replace($analogs, $symbol, $line);
        }

        //Заменяем формы имён
        foreach (self::EDIT_MAP as $name => $analogs) {
            if (in_array($line, $analogs, true)) {
                foreach ($analogs as $analog) {
                    if ($line === $analog) {
                        return $name;
                    }
                }
            }
        }
        return $line;
    }
}
