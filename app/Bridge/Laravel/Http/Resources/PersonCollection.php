<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Resources;

use App\Application\Service\Rank\ActivePersonRankService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PersonCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        $this->preloadActiveRanks();

        return [
            'data' => parent::toArray($request),
        ];
    }

    /**
     * Разряды всей страницы считаются одним батчевым запросом,
     * иначе каждый PersonResource дёргает ActivePersonRankService отдельно (n+1).
     */
    private function preloadActiveRanks(): void
    {
        $personIds = $this->collection
            ->map(static fn (PersonResource $resource): string => (string)$resource->id)
            ->all();

        $ranks = app(ActivePersonRankService::class)->executeForMany($personIds);

        $this->collection->each(static function (PersonResource $resource) use ($ranks): void {
            $resource->activeRank = $ranks[(int)$resource->id] ?? null;
            $resource->rankPreloaded = true;
        });
    }
}
