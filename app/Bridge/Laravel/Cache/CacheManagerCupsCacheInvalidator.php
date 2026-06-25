<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Cache;

use App\Domain\Cup\CupCacheInvalidator;
use Illuminate\Cache\Repository as CacheManager;

final readonly class CacheManagerCupsCacheInvalidator implements CupCacheInvalidator
{
    public function __construct(private CacheManager $cache)
    {
    }

    public function invalidate(int $cupId): void
    {
        $this->cache->tags(['cups', $cupId])->flush();
    }
}
