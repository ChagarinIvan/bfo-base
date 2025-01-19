<?php

declare(strict_types=1);

namespace App\Domain\ProtocolLine;

use App\Domain\Competition\Competition;
use App\Domain\Cup\Cup;
use App\Domain\PersonPayment\PersonPayment;
use App\Domain\Rank\Rank;
use App\Domain\Shared\Criteria;
use Illuminate\Support\Collection;

interface ProtocolLineRepository
{
    public function byId(int $id, array $with = []): ?ProtocolLine;

    public function byCriteria(Criteria $criteria): Collection;

    public function lockOneByCriteria(Criteria $criteria): ?ProtocolLine;

    public function oneByCriteria(Criteria $criteria): ?ProtocolLine;

    public function update(ProtocolLine $protocolLine): void;
}
