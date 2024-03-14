<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Resources;

use App\Models\Club;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Club
 */
class ClubResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
