<?php

declare(strict_types=1);

namespace App\Domain\Cup\Factory;

use App\Domain\Auth\Impression;
use App\Domain\Competition\Competition;
use App\Domain\Cup\Cup;
use App\Domain\Shared\Clock;

final readonly class StandardCupFactory implements CupFactory
{
    public function __construct(private Clock $clock)
    {
    }

    public function create(CupInput $input): Cup
    {
        $cup = new Cup();
        $cup->name = $input->info->name;
        $cup->events_count = $input->info->eventsCount;
        $cup->year = $input->info->year;
        $cup->type = $input->info->type;
        $cup->visible = $input->visible;
        $cup->created = $cup->updated = new Impression($this->clock->now(), $input->userId);

        return $cup;
    }
}
