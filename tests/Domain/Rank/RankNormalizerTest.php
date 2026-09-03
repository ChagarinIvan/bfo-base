<?php

declare(strict_types=1);

namespace Tests\Domain\Rank;

use App\Domain\Rank\Rank;
use App\Domain\Rank\RankNormalizer;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class RankNormalizerTest extends TestCase
{
    #[Test]
    public function it_normalizes_protocol_values_to_enum_cases(): void
    {
        $normalizer = new RankNormalizer();

        $this->assertSame(Rank::CandidateMaster, $normalizer->normalize(' KMC '));
        $this->assertSame(Rank::JuniorFirstRank, $normalizer->normalize('1ю'));
        $this->assertSame(Rank::WithoutRank, $normalizer->normalize('Б/Р'));
        $this->assertNull($normalizer->normalize('-'));
    }

    #[Test]
    public function it_rejects_empty_and_unknown_values(): void
    {
        $normalizer = new RankNormalizer();

        $this->assertNull($normalizer->normalize(null));
        $this->assertNull($normalizer->normalize(''));
        $this->assertFalse($normalizer->isValid('unknown'));
        $this->assertTrue($normalizer->isValid('МС'));
    }
}
