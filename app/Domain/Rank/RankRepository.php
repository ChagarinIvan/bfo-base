<?php

declare(strict_types=1);

namespace App\Domain\Rank;

interface RankRepository
{
    public function add(Rank $rank): void;

    public function lockById(RankId $id): ?Rank;

    public function update(Rank $rank): void;
}
