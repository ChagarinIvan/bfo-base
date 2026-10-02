<?php

declare(strict_types=1);

namespace App\Application\Service\RankCheck;

use App\Application\Service\RankCheck\Exception\RankCheckNotFound;
use App\Domain\Auth\Impression;
use App\Domain\RankCheck\Exception\ProcessError;
use App\Domain\RankCheck\RankCheckRepository;
use App\Domain\Shared\Clock;
use App\Domain\Shared\TransactionManager;

final readonly class FailRankCheckService
{
    public function __construct(
        private RankCheckRepository $checks,
        private TransactionManager $transaction,
        private Clock $clock,
    ) {
    }

    public function execute(FailRankCheck $command): void
    {
        $this->transaction->run(function () use ($command): void {
            $check = $this->checks->lockById($command->id) ?? throw new RankCheckNotFound();

            $check->markFailed(
                new ProcessError()->getMessage(),
                new Impression($this->clock->now(), $check->created->by),
            );

            $this->checks->update($check);
        });
    }
}
