<?php

declare(strict_types=1);

namespace App\Domain\CupEvent;

interface CupEventRepository
{
    public function byId(int $id): ?CupEvent;

    public function lockById(int $id): ?CupEvent;

    public function update(CupEvent $cupEvent): void;
}
