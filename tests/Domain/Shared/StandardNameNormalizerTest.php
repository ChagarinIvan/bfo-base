<?php

declare(strict_types=1);

namespace Tests\Domain\Shared;

use App\Domain\Shared\StandardNameNormalizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Traversable;

final class StandardNameNormalizerTest extends TestCase
{
    public static function names(): Traversable
    {
        yield ['лена', 'елена'];
        yield ['aнна', 'анна'];
        yield ['ваня', 'иван'];
    }
    #[DataProvider('names')]
    #[Test]
    public function it_normalizes_names(string $name, string $expected): void
    {
        $this->assertSame($expected, new StandardNameNormalizer()->normalize($name));
    }
}
