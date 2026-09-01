<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Resources;

use App\Domain\Person\Person;
use App\Domain\Rank\Rank;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Person
 */
final class PersonResource extends JsonResource
{
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
            'rankId' => ($this->current_rank ?? Rank::WithoutRank)->value,
        ];
    }
}
