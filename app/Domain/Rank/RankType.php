<?php

declare(strict_types=1);

namespace App\Domain\Rank;

enum RankType: string
{
    public static function fromString(string $type): self
    {
        return self::from($type);
    }

    public function toString(): string
    {
        return $this->value;
    }
    case WSM_RANK = 'МСМК';
    case SM_RANK = 'МС';
    case SMC_RANK = 'КМС';
    case FIRST_RANK = 'I';
    case SECOND_RANK = 'II';
    case THIRD_RANK = 'III';
    case JUNIOR_FIRST_RANK = 'Iю';
    case JUNIOR_SECOND_RANK = 'IIю';
    case JUNIOR_THIRD_RANK = 'IIIю';
    case WITHOUT_RANK = 'б/р';
}
