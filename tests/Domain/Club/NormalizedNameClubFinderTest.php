<?php

declare(strict_types=1);

namespace Tests\Domain\Club;

use App\Domain\Club\Club;
use App\Domain\Club\ClubRepository;
use App\Domain\Club\NormalizedNameClubFinder;
use App\Domain\Shared\Criteria;
use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class NormalizedNameClubFinderTest extends TestCase
{
    private ClubRepository&MockObject $repository;
    private NormalizedNameClubFinder $finder;

    public static function normalizationCases(): Iterator
    {
        yield 'lowercases input' => ['ТЕСТ КЛУБ', 'тест клуб'];
        yield 'removes double quotes' => ['"тест клуб"', 'тест клуб'];
        yield 'removes guillemets' => ['«тест клуб»', 'тест клуб'];
        yield 'compresses multiple spaces' => ['тест  клуб', 'тест клуб'];
        yield 'replaces ка with ко' => ['тест ка клуб', 'тест ко клуб'];
        yield 'replaces кса with ксо' => ['тест кса клуб', 'тест ксо клуб'];
        yield 'replaces бду with бгу' => ['бду', 'бгу'];
        yield 'replaces bsu with бгу' => ['bsu', 'бгу'];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->finder = new NormalizedNameClubFinder(
            $this->repository = $this->createMock(ClubRepository::class),
        );
    }

    #[Test]
    public function it_returns_club_when_found(): void
    {
        $club = Club::factory()->makeOne();

        $this->repository
            ->expects($this->once())
            ->method('oneByCriteria')
            ->with(new Criteria(['normalizedName' => 'тест клуб']))
            ->willReturn($club);

        $this->assertSame($club, $this->finder->findByName('Тест Клуб'));
    }

    #[Test]
    public function it_returns_null_when_not_found(): void
    {
        $this->repository
            ->expects($this->once())
            ->method('oneByCriteria')
            ->willReturn(null);

        $this->assertNotInstanceOf(Club::class, $this->finder->findByName('Несуществующий Клуб'));
    }

    #[Test]
    #[DataProvider('normalizationCases')]
    public function it_normalizes_name_before_searching(string $input, string $expectedNormalized): void
    {
        $this->repository
            ->expects($this->once())
            ->method('oneByCriteria')
            ->with(new Criteria(['normalizedName' => $expectedNormalized]))
            ->willReturn(null);

        $this->finder->findByName($input);
    }
}
