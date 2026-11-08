<?php

declare(strict_types=1);
namespace App\Domain\Person\ManualRank;
use App\Domain\Shared\Criteria;

interface ManualPersonRankRepository
{
    /** @return list<ManualPersonRank> */
    public function byCriteria(Criteria $criteria): array;
    public function oneByCriteria(Criteria $criteria): ?ManualPersonRank;
    public function add(ManualPersonRank $rank): void;
    public function update(ManualPersonRank $rank): void;
}
