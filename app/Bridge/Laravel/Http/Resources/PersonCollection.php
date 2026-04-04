<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PersonCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [
            'data' => parent::toArray($request),
        ];
    }
}
