<?php

declare(strict_types=1);

namespace App\Infrastracture\Laravel\Eloquent\CupEvent;

use App\Domain\CupEvent\CupEvent;
use App\Domain\CupEvent\CupEventRepository;

final class EloquentCupEventRepository implements CupEventRepository
{
    public function byId(int $id): ?CupEvent
    {
        return CupEvent::where('active', true)->find($id);
    }

    public function lockById(int $id): ?CupEvent
    {
        return CupEvent::where('active', true)->lockForUpdate()->find($id);
    }

    public function update(CupEvent $cupEvent): void
    {
        $cupEvent->save();
    }
}
