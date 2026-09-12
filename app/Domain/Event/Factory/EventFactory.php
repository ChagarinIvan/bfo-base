<?php

declare(strict_types=1);

namespace App\Domain\Event\Factory;

use App\Domain\Event\Event;
use App\Domain\Event\Protocol;

interface EventFactory
{
    public function create(EventInput $input, ?Protocol $protocol = null): Event;
}
