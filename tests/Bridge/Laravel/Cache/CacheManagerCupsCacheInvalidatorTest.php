<?php

declare(strict_types=1);

namespace Tests\Bridge\Laravel\Cache;

use App\Bridge\Laravel\Cache\CacheManagerCupsCacheInvalidator;
use Illuminate\Cache\Repository as CacheManager;
use Illuminate\Cache\TaggedCache;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class CacheManagerCupsCacheInvalidatorTest extends TestCase
{
    private CacheManagerCupsCacheInvalidator $invalidator;

    private CacheManager&MockObject $cache;

    protected function setUp(): void
    {
        parent::setUp();

        $this->invalidator = new CacheManagerCupsCacheInvalidator(
            $this->cache = $this->createMock(CacheManager::class),
        );
    }

    #[Test]
    public function it_flushes_tagged_cache_for_cup(): void
    {
        $taggedCache = $this->createMock(TaggedCache::class);
        $taggedCache->expects($this->once())->method('flush');

        $this->cache
            ->expects($this->once())
            ->method('tags')
            ->with(['cups', 42])
            ->willReturn($taggedCache)
        ;

        $this->invalidator->invalidate(42);
    }
}
