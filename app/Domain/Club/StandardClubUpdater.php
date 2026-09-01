<?php

declare(strict_types=1);

namespace App\Domain\Club;

use App\Domain\Auth\Impression;
use App\Domain\Shared\Clock;

final readonly class StandardClubUpdater implements ClubUpdater
{
    public function __construct(private Clock $clock)
    {
    }

    public function update(Club $club, ClubInput $input): Club
    {
        $club->updateInfo(
            $input->info,
            new Impression($this->clock->now(), $input->userId),
        );

        return $club;
    }
}
