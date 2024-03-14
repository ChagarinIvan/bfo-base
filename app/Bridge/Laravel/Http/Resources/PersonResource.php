<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Resources;

use App\Models\Person;
use App\Services\RankService;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Person
 */
class PersonResource extends JsonResource
{
    private RankService $rankService;

    public function __construct($resource)
    {
        parent::__construct($resource);
        $this->rankService = app(RankService::class);
    }

    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'lastname' => $this->lastname,
            'firstname' => $this->firstname,
            'birthday' => $this->birthday?->format('Y-m-d'),
            'events_count' => $this->protocol_lines_count,
            'club_id' => $this->club_id,
            'club_name' => $this->club?->name,
            'rank' => $this->rankService->getActiveRank($this->id)?->rank
        ];
    }
}
