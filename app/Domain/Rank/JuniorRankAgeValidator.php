<?php

declare(strict_types=1);

namespace App\Domain\Rank;

use App\Domain\Person\PersonRepository;
use App\Domain\Shared\Clock;
use App\Models\Year;
use function in_array;

/**
 * Прысваенне 3ю разрада за 3 паспяховых старта у гадзе
 */
final readonly class JuniorRankAgeValidator
{
    public function __construct(private PersonRepository $persons)
    {
    }

    /**
     * падыходзіць лі гэты юнацкі разряд под узрост, або гэта ўвогуле не юнаці разрад
     */
    public function validate(int $personId, string $rank, Year $year): bool
    {
        if (!in_array($rank, Rank::JUNIOR_RANKS, true)) {
            return true;
        }

        $person = $this->persons->byId($personId);

        if ($person === null) {
            return false;
        }

        $age = $year->value - $person->birthday?->year;

        return $age <= Rank::MAX_JUNIOR_AGE;
    }
}
