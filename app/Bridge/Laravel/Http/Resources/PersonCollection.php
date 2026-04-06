<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PersonCollection extends ResourceCollection
{
    public function __construct($resource, private array $ranks = [])
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection->map(fn ($person) =>
                (new PersonResource($person, $this->ranks))->toArray($request)
            ),
        ];
    }
}
