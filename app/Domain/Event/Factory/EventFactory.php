<?php

declare(strict_types=1);

namespace App\Domain\Event\Factory;

use App\Domain\Event\Event;

interface EventFactory
{
    public function create(EventInput $input): Event;
}
