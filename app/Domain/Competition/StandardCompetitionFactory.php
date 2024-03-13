<?php

declare(strict_types=1);

namespace App\Domain\Competition;

use App\Domain\Auth\Impression;
use App\Domain\Shared\Clock;
use App\Models\Competition;

final readonly class StandardCompetitionFactory implements CompetitionFactory
{
    public function __construct(private Clock $clock)
    {
    }

    public function create(CompetitionInput $input): Competition
    {
        $competition = new Competition();
        $competition->name = $input->name;
        $competition->description = $input->description;
        $competition->from = $input->from;
        $competition->to = $input->to;
        $competition->created = $competition->updated = new Impression($this->clock->now(), $input->userId);

        return $competition;
    }
}
