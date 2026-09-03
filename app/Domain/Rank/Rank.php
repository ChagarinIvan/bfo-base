<?php

declare(strict_types=1);

namespace App\Domain\Rank;

use function in_array;

enum Rank: int
{
    public static function fromProtocolValue(?string $value): ?self
    {
        return new RankNormalizer()->normalize($value);
    }

    /** @return list<self> */
    public static function ordered(): array
    {
        return self::cases();
    }

    public function label(): string
    {
        return match ($this) {
            self::WorldClassMaster => 'МСМК',
            self::MasterOfSport => 'МС',
            self::CandidateMaster => 'КМС',
            self::FirstRank => 'I',
            self::SecondRank => 'II',
            self::ThirdRank => 'III',
            self::JuniorFirstRank => 'Iю',
            self::JuniorSecondRank => 'IIю',
            self::JuniorThirdRank => 'IIIю',
            self::WithoutRank => 'б/р',
        };
    }

    public function isJunior(): bool
    {
        return in_array($this, [self::JuniorFirstRank, self::JuniorSecondRank, self::JuniorThirdRank], true);
    }

    public function isAutomaticallyActivated(): bool
    {
        return !in_array($this, [self::CandidateMaster, self::MasterOfSport, self::WorldClassMaster], true);
    }

    /** @return list<self> */
    public function strongerRanks(): array
    {
        return match ($this) {
            self::ThirdRank => [self::SecondRank, self::FirstRank, self::CandidateMaster, self::MasterOfSport, self::WorldClassMaster],
            self::SecondRank => [self::FirstRank, self::CandidateMaster, self::MasterOfSport, self::WorldClassMaster],
            self::FirstRank => [self::CandidateMaster, self::MasterOfSport, self::WorldClassMaster],
            self::CandidateMaster => [self::MasterOfSport, self::WorldClassMaster],
            self::MasterOfSport => [self::WorldClassMaster],
            default => [],
        };
    }

    case WorldClassMaster = 9;
    case MasterOfSport = 8;
    case CandidateMaster = 7;
    case FirstRank = 6;
    case SecondRank = 5;
    case ThirdRank = 4;
    case JuniorFirstRank = 3;
    case JuniorSecondRank = 2;
    case JuniorThirdRank = 1;
    case WithoutRank = 0;
}
