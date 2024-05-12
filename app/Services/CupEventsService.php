<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\Cup\Cup;
use App\Domain\Cup\CupEvent\CupEvent;
use App\Domain\Cup\CupEvent\CupEventPoint;
use App\Domain\Cup\CupEvent\CupEventRepository;
use App\Domain\Cup\Group\CupGroup;
use App\Domain\Shared\Criteria;
use Illuminate\Cache\Repository as CacheManager;
use Illuminate\Support\Collection;
use RuntimeException;

final readonly class CupEventsService
{
    public function __construct(
        private CacheManager $cache,
        private CupEventRepository $cupEvents,
    ) {
    }

    public function getCupEvents(string $cupId): Collection
    {
        return $this->cupEvents->byCriteria(new Criteria(['cupId' => $cupId]));
    }

    public function getCupEvent(int $cupEventId): CupEvent
    {
        return $this->cupEvents->byId($cupEventId) ?? throw new RuntimeException('Wrong cup event id.');
    }

    /** @return array<string, CupEventPoint[]> */
    public function calculateCup(Cup $cup, Collection $cupEvents, CupGroup $group): array
    {
        return $this->cache->tags(['cups', $cup->id])->remember(
            "{$cup->id}_{$group->id()}",
            1000000,
            static function () use ($cup, $cupEvents, $group) {
                return $cup->type->instance()->calculateCup($cup, $cupEvents, $group);
            }
        );
    }
}
