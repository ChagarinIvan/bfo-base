<?php

declare(strict_types=1);

namespace App\Domain\Competition\Factory;

use App\Domain\Auth\Impression;
use App\Domain\Competition\Competition;
use App\Domain\Shared\Clock;

final readonly class StandardCompetitionFactory implements CompetitionFactory
{
    public function __construct(private Clock $clock)
    {
    }

    public function create(CompetitionInput $input): Competition
    {
        $competition = new Competition();
        $competition->name = $input->info->name;
        $competition->description = $input->info->description;
        $competition->from = $input->info->from;
        $competition->to = $input->info->to;
        $competition->created = $competition->updated = new Impression($this->clock->now(), $input->userId);

        return $competition;
    }
}
