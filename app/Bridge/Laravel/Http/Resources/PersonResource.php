<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Resources;

use App\Application\Service\Rank\ActivePersonRank;
use App\Application\Service\Rank\ActivePersonRankService;
use App\Domain\Person\Person;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Person
 */
final class PersonResource extends JsonResource
{
//    private ActivePersonRankService $activePersonRank;

//    public function __construct($resource)
//    {
//        parent::__construct($resource);
//        $this->activePersonRank = app(ActivePersonRankService::class);
//    }

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
            'rank' => null, //$this->activePersonRank->execute(new ActivePersonRank((string)$this->id))?->rank
        ];
    }
}
