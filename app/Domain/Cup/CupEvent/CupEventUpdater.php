<?php

declare(strict_types=1);

namespace App\Domain\Cup\CupEvent;

use App\Domain\Cup\CupEvent\Exception\CupAlreadyContainsEvent;
use App\Domain\Event\Exception\EventNotExists;

interface CupEventUpdater
{
    /** @throws CupAlreadyContainsEvent|EventNotExists */
    public function update(CupEvent $cupEvent, CupEventUpdateInput $input): CupEvent;
}
