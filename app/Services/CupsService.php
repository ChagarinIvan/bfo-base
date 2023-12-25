<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Cup;
use App\Models\Year;
use Illuminate\Cache\Repository as CacheManager;
use Illuminate\Support\Collection;
use RuntimeException;

final readonly class CupsService
{
    public function __construct(private CacheManager $cache)
    {
    }

    public function getYearCups(Year $year): Collection
    {
        return Cup::where('year', $year)
            ->where('visible', true)
            ->get()
        ;
    }

    public function deleteCup(Cup $cup): void
    {
        $cup->delete();
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
