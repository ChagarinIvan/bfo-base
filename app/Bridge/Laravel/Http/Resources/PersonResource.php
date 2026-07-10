<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Resources;

use App\Application\Dto\Rank\ViewRankDto;
use App\Application\Service\Rank\ActivePersonRank;
use App\Application\Service\Rank\ActivePersonRankService;
use App\Domain\Person\Person;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Person
 */
final class PersonResource extends JsonResource
{
    public ?ViewRankDto $activeRank = null;
    public bool $rankPreloaded = false;

    public function toArray($request): array
    {
        $activeRank = $this->rankPreloaded
            ? $this->activeRank
            : app(ActivePersonRankService::class)->execute(new ActivePersonRank((string)$this->id));

        return [
            'id' => $this->id,
            'lastname' => $this->lastname,
            'firstname' => $this->firstname,
            'birthday' => $this->birthday?->format('Y-m-d'),
            'events_count' => $this->protocol_lines_count,
            'club_id' => $this->club_id,
            'club_name' => $this->club?->name,
            'rank' => $activeRank?->rank,
        ];
    }
}
