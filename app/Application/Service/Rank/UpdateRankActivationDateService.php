<?php

declare(strict_types=1);

namespace App\Application\Service\Rank;

use App\Application\Dto\Rank\RankAssembler;
use App\Application\Dto\Rank\ViewRankDto;
use App\Application\Service\Rank\Criteria\RankProtocolLineCriteria;
use App\Application\Service\Rank\Exception\ProtocolLineNotFound;
use App\Application\Service\Rank\Exception\RankNotFound;
use App\Domain\ProtocolLine\ProtocolLineRepository;
use App\Domain\Rank\RankRepository;
use App\Domain\Shared\TransactionManager;

final readonly class UpdateRankActivationDateService
{
    public function __construct(
        private RankRepository $ranks,
        private ProtocolLineRepository $protocolLines,
        private TransactionManager $transaction,
        private RankAssembler $assembler,
    ) {
    }

    /**
     * @throws RankNotFound
     * @throws ProtocolLineNotFound
     */
    public function execute(UpdateRankActivationDate $command): ViewRankDto
    {
        $rank = $this->ranks->byId($command->id()) ?? throw new RankNotFound;

        $this->transaction->run(function () use ($command, $rank): void {
            $protocolLine = $this->protocolLines->lockOneByCriteria(RankProtocolLineCriteria::create($rank))
                ?? throw new ProtocolLineNotFound;
            $protocolLine->activateRank($command->date());

            $this->protocolLines->update($protocolLine);
        });

        return $this->assembler->toViewRankDto($rank);
    }
}
