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
            (string) $rank->personId,
            $rank->input->rank->value,
            $rank->input->achievedOn->format('Y-m-d'),
            $rank->input->activatedOn->format('Y-m-d'),
            $this->auth->toImpressionDto($rank->created),
            $this->auth->toImpressionDto($rank->updated())
        );
    }
}
