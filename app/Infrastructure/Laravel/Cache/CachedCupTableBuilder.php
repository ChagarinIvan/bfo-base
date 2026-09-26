<?php

declare(strict_types=1);

namespace App\Infrastructure\Laravel\Cache;

use App\Domain\Cup\Cup;
use App\Domain\Cup\Group\CupGroup;
use App\Domain\Cup\Table\CupTable;
use App\Domain\Cup\Table\CupTableBuilder;
use Illuminate\Cache\Repository as CacheManager;
use Illuminate\Support\Collection;

final readonly class CachedCupTableBuilder implements CupTableBuilder
{
    public function __construct(
        private CacheManager $cache,
        private CupTableBuilder $builder,
    ) {
    }

    public function build(Cup $cup, Collection $events, CupGroup $group): CupTable
    {
        return $this->cache->tags(['cups', $cup->id])->remember(
            "table_{$cup->id}_{$group->id()}",
            1000000,
            fn (): CupTable => $this->builder->build($cup, $events, $group),
        );
    }
}
