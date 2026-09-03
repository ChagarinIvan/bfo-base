<?php

declare(strict_types=1);

namespace App\Domain\Rank;

use function mb_strtolower;
use function str_replace;
use function trim;

final class RankNormalizer
{
    public function normalize(?string $value): ?Rank
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $value = mb_strtolower(trim($value));
        $value = str_replace(['к', 'k', 'm', 'c', '/', '\\'], ['к', 'к', 'м', 'с', '', ''], $value);
        $value = match ($value) {
            '1' => 'i', '2' => 'ii', '3' => 'iii',
            '1ю' => 'iю', '2ю' => 'iiю', '3ю' => 'iiiю',
            default => $value,
        };

        return match ($value) {
            'мсмк' => Rank::WorldClassMaster,
            'мс' => Rank::MasterOfSport,
            'кмс' => Rank::CandidateMaster,
            'i' => Rank::FirstRank,
            'ii' => Rank::SecondRank,
            'iii' => Rank::ThirdRank,
            'iю' => Rank::JuniorFirstRank,
            'iiю' => Rank::JuniorSecondRank,
            'iiiю' => Rank::JuniorThirdRank,
            'бр' => Rank::WithoutRank,
            default => null,
        };
    }

    public function isValid(string $value): bool
    {
        return $this->normalize($value) !== null;
    }
}
