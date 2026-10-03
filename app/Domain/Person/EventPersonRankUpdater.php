<?php

declare(strict_types=1);

namespace App\Domain\Person;

use App\Domain\Auth\Impression;
use App\Domain\Event\Event;
use App\Domain\Person\Exception\RanksUpdatingError;

interface EventPersonRankUpdater
{
    /** @throws RanksUpdatingError */
    public function update(Event $event, Impression $impression): void;
}
