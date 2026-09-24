<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\Cup\Cup;
use App\Domain\Cup\CupEvent\CupEventPoint;
use App\Domain\Cup\CupEvent\CupEventRepository;
use App\Domain\Cup\Group\CupGroup;
use App\Domain\Shared\Criteria;
use Illuminate\Cache\Repository as CacheManager;
use Illuminate\Support\Collection;

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

    /** @return array<string, CupEventPoint[]> */
    public function calculateCup(Cup $cup, Collection $cupEvents, CupGroup $group): array
    {
        // Temporary diagnostic bypass. Remove after the ElkPath incident is resolved.
        return $cup->type->instance()->calculateCup($cup, $cupEvents, $group);
    }
}
