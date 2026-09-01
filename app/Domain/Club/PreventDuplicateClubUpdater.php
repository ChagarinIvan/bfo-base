<?php

declare(strict_types=1);

namespace App\Domain\Club;

use App\Domain\Club\Exception\ClubAlreadyExist;

final readonly class PreventDuplicateClubUpdater implements ClubUpdater
{
    public function __construct(
        private ClubUpdater $decorated,
        private ClubRepository $clubs,
    ) {
    }

    public function update(Club $club, ClubInput $input): Club
    {
        $existingClub = $this->clubs->oneByNormalizedName($input->info->normalizeName);

        if ($existingClub instanceof Club && $existingClub->id !== $club->id) {
            throw ClubAlreadyExist::byName($input->info->name);
        }

        return $this->decorated->update($club, $input);
    }
}
