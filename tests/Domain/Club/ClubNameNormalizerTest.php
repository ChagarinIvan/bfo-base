<?php

declare(strict_types=1);

namespace Tests\Domain\Club;

use App\Domain\Club\ClubNameNormalizer;
use App\Domain\Shared\SymbolNormalizer;
use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ClubNameNormalizerTest extends TestCase
{
    public static function normalizationCases(): Iterator
    {
        yield 'trims, lowercases and removes quotes' => ['  ТЕСТ «КЛУБ»  ', 'тест клуб'];
        yield 'compresses multiple spaces' => ['тест  клуб', 'тест клуб'];
        yield 'replaces symbol analogs' => ['ТЕСТ CЛУБ', 'тест слуб'];
        yield 'replaces ка with ко' => ['тест ка клуб', 'тест ко клуб'];
        yield 'replaces кса with ксо' => ['тест кса клуб', 'тест ксо клуб'];
        yield 'replaces bsu with бгу' => ['BSU', 'бгу'];
    }

    #[Test]
    #[DataProvider('normalizationCases')]
    public function it_normalizes_club_name(string $input, string $expected): void
    {
        $normalizer = new ClubNameNormalizer(new SymbolNormalizer);

        $this->assertSame($expected, $normalizer->normalize($input));
    }
}
