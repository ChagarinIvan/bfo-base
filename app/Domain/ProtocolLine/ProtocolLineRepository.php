<?php

declare(strict_types=1);

namespace App\Domain\ProtocolLine;

use App\Domain\Shared\Criteria;
use App\Domain\Shared\Pagination\Slice;
use Illuminate\Support\Collection;

interface ProtocolLineRepository
{
    public function byId(int $id, array $with = []): ?ProtocolLine;

    public function lockById(int $id): ?ProtocolLine;

    public function byCriteria(Criteria $criteria): Collection;

    /** @return Slice<ProtocolLine> */
    public function paginate(
        Criteria $criteria,
        ProtocolLineResources $resources = new ProtocolLineResources(),
    ): Slice;

    public function lockOneByCriteria(Criteria $criteria): ?ProtocolLine;

    public function oneByCriteria(Criteria $criteria): ?ProtocolLine;

    public function update(ProtocolLine $protocolLine): void;
}
