<?php

declare(strict_types=1);

namespace Tests\Application\Service\Rank;

use App\Application\Dto\Rank\RankAssembler;
use App\Application\Dto\Rank\ViewRankDto;
use App\Application\Service\Rank\Exception\RankNotFound;
use App\Application\Service\Rank\ViewRank;
use App\Application\Service\Rank\ViewRankService;
use App\Domain\ProtocolLine\ProtocolLineRepository;
use App\Domain\Rank\Rank;
use App\Domain\Rank\RankRepository;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class ViewRankServiceTest extends TestCase
{
    private ViewRankService $service;

    private RankRepository&MockObject $ranks;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new ViewRankService(
            $this->ranks = $this->createMock(RankRepository::class),
            new RankAssembler(
                $this->createMock(ProtocolLineRepository::class)
            ),
        );
    }

    /** @test */
    public function it_fails_when_rank_not_found(): void
    {
        $this->expectException(RankNotFound::class);

        $this->ranks
            ->expects($this->once())
            ->method('byId')
            ->with($this->equalTo(1))
            ->willReturn(null)
        ;

        $command = new ViewRank('1');
        $this->service->execute($command);
    }

    /** @test */
    public function it_shows_rank(): void
    {
        /** @var Rank $rank */
        $rank = Rank::factory()->makeOne();

        $this->ranks
            ->expects($this->once())
            ->method('byId')
            ->with($this->equalTo(1))
            ->willReturn($rank)
        ;

        $command = new ViewRank('1');
        $result = $this->service->execute($command);

        $this->assertInstanceOf(ViewRankDto::class, $result);
        $this->assertEquals($rank->id, $result->id);
    }
}
