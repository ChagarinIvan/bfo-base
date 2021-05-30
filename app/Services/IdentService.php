<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Person;
use App\Models\ProtocolLine;
use Illuminate\Support\Collection;

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

    private Collection|array $persons;

    public function __construct()
    {
        $this->persons = Person::all();
    }

    /**
     * Можно ускорить за счет сохранения подготовленных строк персоны в базу
     * prepared_line|prepared_line_with_year
     * чагарин_иван  |чагарин_иван_1988
     * попробовать перенети левенштайн на SQL
     *
     * @param ProtocolLine $protocolLine
     * @return int
     */
    public function identPerson(ProtocolLine $protocolLine): int
    {
        $withYear = $protocolLine->year !== null;
        $searchLine = $protocolLine->getIndentLine();
        $searchLine = $this->prepareLine($searchLine);
        $result = new Collection();

        foreach ($this->persons as $person) {
            $personData = [
                $person->lastname,
                $person->firstname,
            ];
            if ($withYear && $person->birthday !== null) {
                $personData[] = $person->birthday->format('Y');
            }
            $personLine = mb_strtolower(implode('_', $personData));
            $personLine = $this->prepareLine($personLine);

            $rank = levenshtein($searchLine, $personLine);
            $result->push([
                'id' => $person->id,
                'rank' => $rank,
            ]);

            foreach ($person->getPrompts() as $prompt) {
                $prompt = $this->prepareLine($prompt);
                $rank = levenshtein($searchLine, $prompt);
                $result->push([
                    'id' => $person->id,
                    'rank' => $rank,
                ]);
            }
        }

        $result = $result->groupBy('rank');
        $result = $result->toArray();
        ksort($result);
        $minRank = array_key_first($result);
        if ($minRank <= 5) {
            $result = reset($result);
            $result = reset($result);
            return $result['id'];
        }
        return 0;
    }

    private function prepareLine(string $line): string
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
