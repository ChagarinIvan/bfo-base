<?php

namespace App\Infrastracture\Laravel\Repository;

use App\Domain\Club\Club;
use App\Domain\Club\Repository\ClubRepository;
use Illuminate\Contracts\Database\Query\Builder;

final class OrmClubRepository implements ClubRepository
{
    public function __construct(private readonly Builder $builder)
    {}

    public function byId(int $id): ?Club
    {
        $club = $this->builder
            ->from('club')
            ->where('id', '=', $id)
            ->first();

        dump($club);
        dd($club);

        return $club;
    }
}
