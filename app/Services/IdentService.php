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
    private Collection|array $persons;

    public function __construct()
    {
        $this->persons = Person::all();
    }

    public function identPerson(ProtocolLine $protocolLine): int
    {
        $withYear = $protocolLine->year !== null;
        $searchLine = $protocolLine->getIndentLine();
        $result = new Collection();

        foreach ($this->persons as $person) {
            $personData = [
                $person->lastname,
                $person->firstname,
            ];
            if ($withYear && $person->birthday !== null) {
                $personData[] = $person->birthday->format('Y');
            }
            $personLine = strtolower(implode('_', $personData));
            $rank = levenshtein($searchLine, $personLine);
            $result->push([
                'id' => $person->id,
                'rank' => $rank,
            ]);

            foreach ($person->getPrompts() as $prompt) {
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
}
