<?php

declare(strict_types=1);

namespace Tests\Domain\Group;

use App\Domain\Group\GroupNameNormalizer;
use App\Domain\Shared\SymbolNormalizer;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class GroupNameNormalizerTest extends TestCase
{
    #[Test]
    public function it_trims_normalizes_spaces_and_symbol_analogs(): void
    {
        $normalizer = new GroupNameNormalizer(new SymbolNormalizer);

        $this->assertSame('м21 с', $normalizer->normalize('  М21   C  '));
    }
}
