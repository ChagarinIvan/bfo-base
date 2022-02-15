<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class PersonCollection extends ResourceCollection
{
    public function toArray($request): array
    {
        return [
            'data' => parent::toArray($request),
        ];
    }
}
