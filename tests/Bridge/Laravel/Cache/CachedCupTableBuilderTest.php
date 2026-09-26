<?php

declare(strict_types=1);

namespace Tests\Bridge\Laravel\Cache;

use App\Domain\Cup\Cup;
use App\Domain\Cup\Group\CupGroupFactory;
use App\Domain\Cup\Table\CupTable;
use App\Domain\Cup\Table\CupTableBuilder;
use App\Infrastructure\Laravel\Cache\CachedCupTableBuilder;
use Illuminate\Cache\Repository as CacheManager;
use Illuminate\Cache\TaggedCache;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class CachedCupTableBuilderTest extends TestCase
{
    #[Test]
    public function it_caches_tables_with_the_same_tag_as_cup_calculations(): void
    {
        $cup = $this->getMockBuilder(Cup::class)
            ->onlyMethods([])
            ->getMock()
        ;
        $cup->setAttribute('id', 42);
        $group = CupGroupFactory::fromId('M_0_');
        $events = new Collection();
        $table = new CupTable([], []);
        $builder = $this->createMock(CupTableBuilder::class);
        $builder->expects($this->once())
            ->method('build')
            ->with($cup, $events, $group)
            ->willReturn($table)
        ;

        $taggedCache = $this->createMock(TaggedCache::class);
        $taggedCache->expects($this->once())
            ->method('remember')
            ->with('table_v2_42_M_0_', 1000000, $this->callback('is_callable'))
            ->willReturnCallback(static fn (string $key, int $ttl, callable $callback): CupTable => $callback())
        ;

        $cache = $this->createMock(CacheManager::class);
        $cache->expects($this->once())
            ->method('tags')
            ->with(['cups', 42])
            ->willReturn($taggedCache)
        ;

        $result = new CachedCupTableBuilder($cache, $builder)->build($cup, $events, $group);

        $this->assertSame($table, $result);
    }
}
