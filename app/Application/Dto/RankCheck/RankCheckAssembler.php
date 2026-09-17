<?php

declare(strict_types=1);

namespace App\Application\Dto\RankCheck;

use App\Application\Dto\Auth\AuthAssembler;
use App\Domain\RankCheck\RankCheck;

final readonly class RankCheckAssembler
{
    public function __construct(private AuthAssembler $authAssembler)
    {
    }

    public function toViewRankCheckDto(RankCheck $check): ViewRankCheckDto
    {
        return new ViewRankCheckDto(
            id: (string) $check->id,
            status: $check->status->value,
            created: $this->authAssembler->toImpressionDto($check->created),
            updated: $this->authAssembler->toImpressionDto($check->updated),
            error: $check->error_message,
        );
    }
}
