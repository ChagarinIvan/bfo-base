<?php

declare(strict_types=1);

namespace Tests\Models;

use App\Domain\Rank\Rank;
use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RankTest extends TestCase
{
    public static function ranksProvider(): Iterator
    {
        yield 0 => [true, 'КМС'];
        yield 1 => [true, 'Iю'];
        yield 2 => [true, 'МСМК'];
        yield 3 => [true, 'IIю'];
        yield 4 => [true, 'КМС'];
        yield 5 => [true, 'КМC'];
        yield 6 => [true, 'KМС'];
        yield 7 => [true, 'кмс'];
        yield 8 => [true, 'kmc'];
        yield 9 => [true, 'бр'];
        yield 10 => [true, 'б/р'];
        yield 11 => [true, 'Б/Р'];
        yield 12 => [true, 'МС'];
        yield 13 => [false, '-'];
        yield 14 => [false, ''];
        yield 15 => [true, '1'];
        yield 16 => [true, '1ю'];
    }
    #[DataProvider('ranksProvider')]
    #[Test]
    public function validate_rank(bool $expectedValidationResult, string $rank): void
    {
        $this->assertSame($expectedValidationResult, Rank::validateRank($rank));
    }
}
