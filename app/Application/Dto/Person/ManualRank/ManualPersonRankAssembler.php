<?php

declare(strict_types=1);

namespace App\Application\Dto\Person\ManualRank;

use App\Application\Dto\Auth\AuthAssembler;
use App\Domain\Person\ManualRank\ManualPersonRank;

final readonly class ManualPersonRankAssembler
{
    public function __construct(private AuthAssembler $auth)
    {
    }

    public function toDto(ManualPersonRank $rank): ViewManualPersonRankDto
    {
        return new ViewManualPersonRankDto(
            $rank->id,
            (string) $rank->person_id,
            $rank->rank->value,
            $rank->achieved_on->format('Y-m-d'),
            $rank->activated_on->format('Y-m-d'),
            $this->auth->toImpressionDto($rank->created),
            $this->auth->toImpressionDto($rank->updated)
        );
    }
}
