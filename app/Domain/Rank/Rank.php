<?php

declare(strict_types=1);

namespace App\Domain\Rank;

use function array_map;
use function in_array;
use function mb_strtolower;
use function str_replace;
use function trim;

enum Rank: string
{
    public const WSM_RANK = 'МСМК';
    public const SM_RANK = 'МС';
    public const SMC_RANK = 'КМС';
    public const FIRST_RANK = 'I';
    public const SECOND_RANK = 'II';
    public const THIRD_RANK = 'III';
    public const JUNIOR_FIRST_RANK = 'Iю';
    public const JUNIOR_SECOND_RANK = 'IIю';
    public const JUNIOR_THIRD_RANK = 'IIIю';
    public const WITHOUT_RANK = 'б/р';
    public const MAX_JUNIOR_AGE = 18;

    public const array JUNIOR_RANKS = [self::JUNIOR_FIRST_RANK, self::JUNIOR_SECOND_RANK, self::JUNIOR_THIRD_RANK];
    public const array NEXT_RANKS = [
        self::SM_RANK => self::WSM_RANK,
        self::SMC_RANK => self::SM_RANK,
        self::FIRST_RANK => self::SMC_RANK,
        self::SECOND_RANK => self::FIRST_RANK,
        self::THIRD_RANK => self::SECOND_RANK,
    ];
    public const array RANKS = [
        self::WSM_RANK,
        self::SM_RANK,
        self::SMC_RANK,
        self::FIRST_RANK,
        self::SECOND_RANK,
        self::THIRD_RANK,
        self::JUNIOR_FIRST_RANK,
        self::JUNIOR_SECOND_RANK,
        self::JUNIOR_THIRD_RANK,
    ];

    public static function autoActivation(?string $value): bool
    {
        return ($rank = self::fromProtocolValue($value)) !== null && $rank->isAutomaticallyActivated();
    }

    /** @return list<string> */
    public static function strongerRank(string $value): array
    {
        $rank = self::fromProtocolValue($value);

        return $rank === null ? [] : array_map(static fn (self $item): string => $item->label(), $rank->strongerRanks());
    }

    public static function fromProtocolValue(?string $value): ?self
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
            'мсмк' => self::WorldClassMaster,
            'мс' => self::MasterOfSport,
            'кмс' => self::CandidateMaster,
            'i' => self::FirstRank,
            'ii' => self::SecondRank,
            'iii' => self::ThirdRank,
            'iю' => self::JuniorFirstRank,
            'iiю' => self::JuniorSecondRank,
            'iiiю' => self::JuniorThirdRank,
            'бр' => self::WithoutRank,
            default => null,
        };
    }

    public static function validateProtocolValue(string $value): bool
    {
        return self::fromProtocolValue($value) !== null;
    }

    /** @deprecated Parser compatibility; new Domain code uses fromProtocolValue(). */
    public static function validateRank(string $value): bool
    {
        return self::validateProtocolValue($value);
    }

    /** @deprecated Parser compatibility; new Domain code uses fromProtocolValue(). */
    public static function getRank(?string $value): ?string
    {
        return self::fromProtocolValue($value)?->label();
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

    public function strength(): int
    {
        return match ($this) {
            self::WithoutRank => 0,
            self::JuniorThirdRank => 1,
            self::JuniorSecondRank => 2,
            self::JuniorFirstRank => 3,
            self::ThirdRank => 4,
            self::SecondRank => 5,
            self::FirstRank => 6,
            self::CandidateMaster => 7,
            self::MasterOfSport => 8,
            self::WorldClassMaster => 9,
        };
    }
    case WorldClassMaster = 'world_class_master';
    case MasterOfSport = 'master_of_sport';
    case CandidateMaster = 'candidate_master';
    case FirstRank = 'first_rank';
    case SecondRank = 'second_rank';
    case ThirdRank = 'third_rank';
    case JuniorFirstRank = 'junior_first_rank';
    case JuniorSecondRank = 'junior_second_rank';
    case JuniorThirdRank = 'junior_third_rank';
    case WithoutRank = 'without_rank';
}
