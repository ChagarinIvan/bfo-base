<?php

namespace Models;

use App\Models\Rank;
use PHPUnit\Framework\TestCase;

class RankTest extends TestCase
{
    /**
     * @param bool $expectedValidationResult
     * @param string $rank
     * @dataProvider ranksProvider
     */
    public function testValidateRank(bool $expectedValidationResult, string $rank): void
    {
        $this->assertEquals($expectedValidationResult, Rank::validateRank($rank));
    }

    public function ranksProvider(): array
    {
        return [
            0 => [true, 'КМС'],
            1 => [true, 'Iю'],
            2 => [true, 'МСМК'],
            3 => [true, 'IIю'],
            4 => [true, 'КМС'],
            5 => [true, 'КМC'],
            6 => [true, 'KМС'],
            7 => [true, 'кмс'],
            8 => [true, 'kmc'],
            9 => [true, 'бр'],
            10 => [true, 'б/р'],
            11 => [true, 'Б/Р'],
            12 => [true, 'МС'],
            13 => [false, '-'],
            14 => [false, ''],
            15 => [false, '1'],
            16 => [true, '1ю'],
        ];
    }
}