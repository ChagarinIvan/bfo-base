<?php

declare(strict_types=1);

namespace Tests\Application\Service\Rank;

use App\Application\Dto\Rank\ActivationDto;
use App\Application\Dto\Rank\RankAssembler;
use App\Application\Service\Rank\ActivateRank;
use App\Application\Service\Rank\ActivateRankService;
use App\Application\Service\Rank\Exception\ProtocolLineNotFound;
use App\Application\Service\Rank\Exception\RankNotFound;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\ProtocolLine\ProtocolLineRepository;
use App\Domain\Rank\Rank;
use App\Domain\Rank\RankRepository;
use App\Domain\Shared\Criteria;
use App\Domain\Shared\DummyTransactional;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class ActivateRankServiceTest extends TestCase
{
    private ActivateRankService $service;

    private RankRepository&MockObject $ranks;

    private ProtocolLineRepository&MockObject $protocolLines;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new ActivateRankService(
            $this->ranks = $this->createMock(RankRepository::class),
            $this->protocolLines = $this->createMock(ProtocolLineRepository::class),
            new DummyTransactional,
            new RankAssembler,
        );
    }

    /** @test */
    public function it_fails_on_rank_not_found(): void
    {
        $this->expectException(RankNotFound::class);

        $this->ranks
            ->expects($this->once())
            ->method('byId')
            ->with($this->equalTo(1))
        ;
        $this->protocolLines->expects($this->never())->method('lockOneByCriteria');

        $dto = new ActivationDto();
        $dto->date = '2024-02-01';

        $command = new ActivateRank('1', $dto);
        $this->service->execute($command);
    }

    /** @test */
    public function it_fails_on_protocol_line_not_found(): void
    {
        $this->expectException(ProtocolLineNotFound::class);

        /** @var Rank $rank */
        $rank = Rank::factory()->makeOne(['person_id' => 1, 'event_id' => 2, 'activated_date' => null]);

        $this->ranks
            ->expects($this->once())
            ->method('byId')
            ->with($this->equalTo(1))
            ->willReturn($rank)
        ;

        $this->protocolLines
            ->expects($this->once())
            ->method('lockOneByCriteria')
            ->with($this->equalTo(new Criteria(['personId' => 1, 'eventId' => 2])))
        ;

        $dto = new ActivationDto();
        $dto->date = '2024-02-01';

        $command = new ActivateRank('1', $dto);
        $this->service->execute($command);
    }

    /** @test */
    public function it_activates_rank(): void
    {
        /** @var Rank $rank */
        $rank = Rank::factory()->makeOne(['person_id' => 1, 'event_id' => 2, 'activated_date' => null]);
        /** @var ProtocolLine $line */
        $line = ProtocolLine::factory()->makeOne(['person_id' => 1]);

        $this->ranks
            ->expects($this->once())
            ->method('byId')
            ->with($this->equalTo(1))
            ->willReturn($rank)
        ;

        $this->protocolLines
            ->expects($this->once())
            ->method('lockOneByCriteria')
            ->with($this->equalTo(new Criteria(['personId' => 1, 'eventId' => 2])))
            ->willReturn($line)
        ;

        $dto = new ActivationDto();
        $dto->date = '2024-02-01';

        $command = new ActivateRank('1', $dto);
        $this->service->execute($command);
    }
}
