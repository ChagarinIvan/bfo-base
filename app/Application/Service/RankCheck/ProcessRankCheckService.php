<?php

declare(strict_types=1);

namespace App\Application\Service\RankCheck;

use App\Application\Service\RankCheck\Exception\RankCheckNotFound;
use App\Domain\Auth\Impression;
use App\Domain\RankCheck\RankCheckProcessor;
use App\Domain\RankCheck\RankCheckRepository;
use App\Domain\Shared\Clock;
use App\Domain\Shared\TransactionManager;

final readonly class ProcessRankCheckService
{
    public function __construct(
        private RankCheckRepository $checks,
        private RankCheckProcessor $processor,
        private TransactionManager $transaction,
        private Clock $clock,
    ) {
    }

    /** @throws RankCheckNotFound */
    public function execute(ProcessRankCheck $command): void
    {
        $this->transaction->run(function () use ($command): void {
            $check = $this->checks->lockById($command->id) ?? throw new RankCheckNotFound();

            $check->process($this->processor, new Impression($this->clock->now(), $check->created->by));
            $this->checks->update($check);
        });
    }
}
