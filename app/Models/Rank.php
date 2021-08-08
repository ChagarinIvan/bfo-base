<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Class ProtocolLine
 */
class Rank
{
    public const WSM_RANK = 'МСМК';
    public const SMC_RANK = 'КМС';
    public const SM_RANK = 'МС';
    public const FIRST_RANK = 'I';
    public const SECOND_RANK = 'II';
    public const THIRD_RANK = 'III';
    public const UNIOR_FIRST_RANK = 'Iю';
    public const UNIOR_SECOND_RANK = 'IIю';
    public const UNIOR_THIRD_RANK = 'IIIю';


    public const RANKS = [
        self::WSM_RANK,
        self::SMC_RANK,
        self::SM_RANK,
        self::FIRST_RANK,
        self::SECOND_RANK,
        self::THIRD_RANK,
        self::UNIOR_FIRST_RANK,
        self::UNIOR_SECOND_RANK,
        self::UNIORI_THIRD_RANK,
    ];
}
