<?php

declare(strict_types=1);

namespace App\Domain\Person\ManualRank;

use App\Domain\Auth\Impression;
use App\Domain\Person\ManualRank\Exception\DuplicateManualRank;
use App\Domain\Person\ManualRank\Exception\InvalidManualRank;
use App\Domain\Shared\Clock;
use App\Domain\Shared\Criteria;
use App\Domain\Shared\UuidGenerator;

final readonly class ManualPersonRankFactory
{
    public function __construct(private ManualPersonRankRepository $ranks, private Clock $clock, private UuidGenerator $ids)
    {
    }

    public function create(int $personId, ManualRankInput $input, int $userId): ManualPersonRank
    {
        $now = $this->clock->now();
        if ($input->achievedOn > $now->copy()->startOfDay()) {
            throw new InvalidManualRank();
        }
        if ($this->ranks->oneByCriteria(new Criteria([
            'personId' => $personId, 'rank' => $input->rank->value,
            'achievedOn' => $input->achievedOn->format('Y-m-d'),
            'activatedOn' => $input->activatedOn->format('Y-m-d'),
        ])) instanceof ManualPersonRank) {
            throw new DuplicateManualRank();
        }
        $impression = new Impression($now, $userId);
        return new ManualPersonRank($this->ids->generate(), $personId, $input, $impression, $impression);
    }
}
