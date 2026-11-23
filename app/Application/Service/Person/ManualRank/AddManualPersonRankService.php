<?php

declare(strict_types=1);

namespace App\Application\Service\Person\ManualRank;

use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Person\ManualRank\ManualPersonRankAssembler;
use App\Application\Dto\Person\ManualRank\ViewManualPersonRankDto;
use App\Application\Service\Person\Exception\PersonNotFound;
use App\Application\Service\Person\ManualRank\Exception\InvalidManualRankDates;
use App\Application\Service\Person\ManualRank\Exception\ManualRankAlreadyExists;
use App\Application\Service\Person\RebuildPersonRanks;
use App\Application\Service\Person\RebuildPersonRanksService;
use App\Domain\Person\ManualRank\Exception\DuplicateManualRank;
use App\Domain\Person\ManualRank\Exception\InvalidManualRank;
use App\Domain\Person\ManualRank\ManualPersonRankFactory;
use App\Domain\Person\ManualRank\ManualPersonRankRepository;
use App\Domain\Person\PersonRepository;
use App\Domain\Person\RankCalculationReason;
use App\Domain\Shared\TransactionManager;

final readonly class AddManualPersonRankService
{
    public function __construct(
        private PersonRepository $persons,
        private ManualPersonRankRepository $ranks,
        private ManualPersonRankFactory $factory,
        private RebuildPersonRanksService $rebuild,
        private TransactionManager $transaction,
        private ManualPersonRankAssembler $assembler
    )
    {
    }

    public function execute(AddManualPersonRank $command): ViewManualPersonRankDto
    {
        return $this->transaction->run(function () use ($command): ViewManualPersonRankDto {
            $this->persons->lockById($command->personId) ?? throw new PersonNotFound();
            try {
                $rank = $this->factory->create($command->personId, $command->input, $command->userId);
            } catch (DuplicateManualRank) {
                throw new ManualRankAlreadyExists();
            } catch (InvalidManualRank) {
                throw new InvalidManualRankDates();
            }
            $this->ranks->add($rank);
            $this->rebuild->execute(new RebuildPersonRanks($command->personId, new UserId($command->userId), RankCalculationReason::ManualRankAdded));
            return $this->assembler->toDto($rank);
        });
    }
}
