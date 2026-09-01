<?php

declare(strict_types=1);

namespace App\Application\Service\Rank;

use App\Domain\Rank\Rank;
use function array_map;

final readonly class ListRanks
{
    /** @return list<array{id: string, label: string}> */
    public function execute(): array
    {
        return array_map(static fn (Rank $rank): array => ['id' => $rank->value, 'label' => $rank->label()], Rank::cases());
    }
}
