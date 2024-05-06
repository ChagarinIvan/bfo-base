<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\Cup\Cup;
use Illuminate\Cache\Repository as CacheManager;
use RuntimeException;

final readonly class CupsService
{
    public function __construct(private CacheManager $cache)
    {
    }

    public function clearCupCache(int $cupId): void
    {
        $this->cache->tags(['cups', $cupId])->flush();
    }

    public function getCup(int $cupId): Cup
    {
        return Cup::find($cupId) ?? throw new RuntimeException('Wrong cup id.');
    }
}
