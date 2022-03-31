<?php

namespace App\Services;

use App\Models\Cup;
use App\Models\Year;
use Illuminate\Cache\Repository as CacheManager;
use Illuminate\Support\Collection;

class CupsService
{
    public function __construct(private CacheManager $cache)
    {}

    public function getYearCups(Year $year): Collection
    {
        return Cup::whereYear($year)->get();
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
        $cup = Cup::find($cupId);
        if ($cup) {
            return $cup;
        }
        throw new \RuntimeException('Wrong cup id.');
    }
}
