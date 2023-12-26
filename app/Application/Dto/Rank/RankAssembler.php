<?php

declare(strict_types=1);

namespace App\Application\Dto\Rank;

use App\Application\Dto\Auth\AuthAssembler;
use App\Domain\Rank\Rank;
use DateTimeImmutable;

final readonly class RankAssembler
{
    public function __construct(private AuthAssembler $authAssembler)
    {
    }

    public function toViewRankDto(Rank $rank): ViewRankDto
    {
        return new ViewRankDto(
            id: $rank->id()->toString(),
            personId: (string) $rank->personId(),
            type: $rank->type()->toString(),
            eventId: $rank->eventId() ? (string) $rank->eventId() : null,
            completedAt: $rank->completedAt()->format(DateTimeImmutable::ATOM),
            startedAt: $rank->startedAt()?->format(DateTimeImmutable::ATOM),
            finishedAt: $rank->finishedA()?->format(DateTimeImmutable::ATOM),
            created: $this->authAssembler->toImpressionDto($rank->created()),
            updated: $this->authAssembler->toImpressionDto($rank->updated()),
        );
    }
}
