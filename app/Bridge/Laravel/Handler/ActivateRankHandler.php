<?php

declare(strict_types=1);

namespace App\Bridge\Laravel\Handler;

use App\Application\Service\Rank\ActivateRank;
use App\Application\Service\Rank\ActivateRankService;
use App\Domain\Rank\Event\RankAdded;
use App\Domain\Rank\RankType;
use DateTimeImmutable;
use function in_array;

final readonly class ActivateRankHandler
{
    private const SUPPORTED_TYPES = [
        RankType::SMC_RANK,
        RankType::FIRST_RANK,
        RankType::SECOND_RANK,
        RankType::THIRD_RANK,
        RankType::JUNIOR_FIRST_RANK,
        RankType::JUNIOR_SECOND_RANK,
        RankType::JUNIOR_THIRD_RANK,
        RankType::WITHOUT_RANK,
    ];

    public function __construct(private ActivateRankService $activateRankService)
    {
    }

    public function __invoke(RankAdded $event): void
    {
        if (!in_array($event->type, self::SUPPORTED_TYPES, true)) {
            return;
        }

        $command = new ActivateRank(
            $event->id->toString(),
            $event->completedAt->format(DateTimeImmutable::ATOM),
            $event->created->by->toString(),
        );

        $this->activateRankService->execute($command);
    }
}
