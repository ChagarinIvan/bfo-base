<?php

namespace App\Application\Dto\Club;

use App\Domain\Club\Club;

final class ClubAssembler
{
    public function toViewCluDto(Club $club): ViewClubDto
    {
        return new ViewClubDto($club->id(), $club->name());
    }
}
