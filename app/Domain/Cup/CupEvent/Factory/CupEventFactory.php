<?php

declare(strict_types=1);

namespace App\Domain\Cup\CupEvent\Factory;

use App\Domain\Cup\CupEvent\CupEvent;
use App\Domain\Cup\CupEvent\Exception\CupAlreadyContainsEvent;
use App\Domain\Cup\Exception\CupNotExists;
use App\Domain\Event\Exception\EventNotExists;

interface CupEventFactory
{
    /** @throws CupNotExists|EventNotExists|CupAlreadyContainsEvent */
    public function create(CupEventInput $input): CupEvent;
}
