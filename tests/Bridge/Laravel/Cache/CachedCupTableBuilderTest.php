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
    public function it_caches_filtered_and_full_tables_separately_with_the_shared_cups_tag(): void
    {
        $cup = new Cup();
        $cup->setAttribute('id', 42);
        $group = CupGroupFactory::fromId('M_0_');
        $events = new Collection();
        $table = new CupTable([], []);
        $filteredTable = new CupTable([], []);
        $flags = [];
        $builder = $this->createMock(CupTableBuilder::class);
        $builder->expects($this->exactly(2))
            ->method('build')
            ->willReturnCallback(function (Cup $builtCup, Collection $builtEvents, object $builtGroup, bool $excludeZeroPointRows) use ($cup, $events, $group, $table, $filteredTable, &$flags): CupTable {
                $this->assertSame($cup, $builtCup);
                $this->assertSame($events, $builtEvents);
                $this->assertSame($group, $builtGroup);
                $flags[] = $excludeZeroPointRows;

                return $excludeZeroPointRows ? $filteredTable : $table;
            })
        ;

        $keys = [];
        $taggedCache = $this->createMock(TaggedCache::class);
        $taggedCache->expects($this->exactly(2))
            ->method('remember')
            ->willReturnCallback(static function (string $key, int $ttl, callable $callback) use (&$keys): CupTable {
                $keys[] = $key;

                return $callback();
            })
        ;

        $cache = $this->createMock(CacheManager::class);
        $cache->expects($this->exactly(2))
            ->method('tags')
            ->with(['cups'])
            ->willReturn($taggedCache)
        ;

        $cached = new CachedCupTableBuilder($cache, $builder);
        $result = $cached->build($cup, $events, $group);
        $filteredResult = $cached->build($cup, $events, $group, excludeZeroPointRows: true);

        $this->assertSame($table, $result);
        $this->assertSame($filteredTable, $filteredResult);
        $this->assertSame([false, true], $flags);
        $this->assertSame(['table_42_M_0_', 'table_42_M_0__nonzero'], $keys);
    }
}
