<?php

declare(strict_types=1);
namespace App\Application\Service\Person\ManualRank;
use App\Application\Dto\Auth\UserId;
use App\Application\Service\Person\Exception\PersonNotFound;
use App\Application\Service\Person\ManualRank\Exception\ManualRankNotFound;
use App\Application\Service\Person\RebuildPersonRanks;
use App\Application\Service\Person\RebuildPersonRanksService;
use App\Domain\Auth\Impression;
use App\Domain\Person\ManualRank\ManualPersonRankRepository;
use App\Domain\Person\PersonRepository;
use App\Domain\Person\RankCalculationReason;
use App\Domain\Shared\Clock;
use App\Domain\Shared\Criteria;
use App\Domain\Shared\TransactionManager;
final readonly class DisableManualPersonRankService
{
    public function __construct(
        private PersonRepository $persons,
        private ManualPersonRankRepository $ranks,
        private RebuildPersonRanksService $rebuild,
        private TransactionManager $transaction,
        private Clock $clock
    )
    {
    }
    public function execute(DisableManualPersonRank $command): void
    {
        $this->transaction->run(function () use ($command): void {
            $this->persons->lockById($command->personId) ?? throw new PersonNotFound();
            $rank = $this->ranks->oneByCriteria(new Criteria(['id' => $command->manualRankId, 'personId' => $command->personId])) ?? throw new ManualRankNotFound();
            $rank->disable(new Impression($this->clock->now(), $command->userId()));
            $this->ranks->update($rank);
            $this->rebuild->execute(new RebuildPersonRanks($command->personId, new UserId($command->userId()), RankCalculationReason::ManualRankRemoved));
        });
    }
}
