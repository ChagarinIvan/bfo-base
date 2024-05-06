<?php

declare(strict_types=1);

namespace App\Infrastracture\Laravel\Eloquent\CupEvent;

use App\Domain\CupEvent\CupEvent;
use App\Domain\CupEvent\CupEventRepository;

final class EloquentCupEventRepository implements CupEventRepository
{
    public function byId(int $id): ?CupEvent
    {
        // TODO ADD ACTIVE CHECK
        return CupEvent::find($id);
    }
}
