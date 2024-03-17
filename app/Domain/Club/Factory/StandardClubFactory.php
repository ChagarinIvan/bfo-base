<?php

declare(strict_types=1);

namespace App\Domain\Club\Factory;

use App\Domain\Auth\Impression;
use App\Domain\Club\Club;
use App\Domain\Shared\Clock;

final readonly class StandardClubFactory implements ClubFactory
{
    public function __construct(
        private ClubNameNormalizer $normalizer,
        private Clock $clock,
    ) {
    }

    public function create(ClubInput $input): Club
    {
        $club = new Club();
        $club->name = $input->name;
        $club->normalize_name = $this->normalizer->normalize($input->name);

        $club->created = $club->updated = new Impression($this->clock->now(), $input->userId);

        return $club;
    }
}
