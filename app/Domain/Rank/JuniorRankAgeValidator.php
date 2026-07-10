<?php

declare(strict_types=1);

namespace App\Domain\Rank;

use App\Domain\Person\Person;
use App\Domain\Person\PersonRepository;
use App\Domain\Shared\Clock;
use App\Models\Year;
use function array_key_exists;
use function in_array;

/**
 * Прысваенне 3ю разрада за 3 паспяховых старта у гадзе
 */
final class JuniorRankAgeValidator
{
    /** @var array<int, Person|null> */
    private array $personsCache = [];

    public function __construct(private readonly PersonRepository $persons)
    {
    }

    /**
     * Прогревает кэш персон, чтобы validate() не ходил в базу по одной персоне.
     *
     * @param iterable<Person> $persons
     * @param array<int|string> $missingIds ids, для которых персона не нашлась (кэшируем null)
     */
    public function warmUp(iterable $persons, array $missingIds = []): void
    {
        foreach ($persons as $person) {
            $this->personsCache[$person->id] = $person;
        }

        foreach ($missingIds as $id) {
            $this->personsCache[(int) $id] ??= null;
        }
    }

    /**
     * падыходзіць лі гэты юнацкі разряд под узрост, або гэта ўвогуле не юнаці разрад
     */
    public function validate(int $personId, string $rank, Year $year): bool
    {
//        dump('$rank :' . $rank);
        if (!in_array($rank, Rank::JUNIOR_RANKS, true)) {
            return true;
        }

        if (!array_key_exists($personId, $this->personsCache)) {
            $this->personsCache[$personId] = $this->persons->byId($personId);
        }

        $person = $this->personsCache[$personId];

        if ($person === null) {
            return false;
        }

        $age = $year->value - $person->birthday?->year;
//        dump('$age :' . $age);
        return $age <= Rank::MAX_JUNIOR_AGE;
    }
}
