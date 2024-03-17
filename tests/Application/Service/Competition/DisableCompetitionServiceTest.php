<?php

declare(strict_types=1);

namespace Tests\Application\Service\Competition;

use App\Application\Dto\Auth\UserId;
use App\Application\Service\Competition\DisableCompetition;
use App\Application\Service\Competition\DisableCompetitionService;
use App\Application\Service\Competition\Exception\CompetitionNotFound;
use App\Domain\Competition\Competition;
use App\Domain\Competition\CompetitionRepository;
use App\Domain\Shared\DummyTransactional;
use App\Domain\Shared\FrozenClock;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class DisableCompetitionServiceTest extends TestCase
{
    private DisableCompetitionService $service;

    private CompetitionRepository&MockObject $competitions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new DisableCompetitionService(
            $this->competitions = $this->createMock(CompetitionRepository::class),
            new FrozenClock(),
            new DummyTransactional(),
        );
    }

    /** @test */
    public function it_fails_when_competition_not_found(): void
    {
        $this->expectException(CompetitionNotFound::class);

        $this->competitions
            ->expects($this->once())
            ->method('lockById')
            ->with($this->equalTo(1))
            ->willReturn(null)
        ;

        $command = new DisableCompetition('1', new UserId(1));
        $this->service->execute($command);
    }

    /** @test */
    public function it_disables_competition(): void
    {
        /** @var Competition $competition */
        $competition = Competition::factory()->makeOne();

        $this->competitions
            ->expects($this->once())
            ->method('lockById')
            ->with($this->equalTo(1))
            ->willReturn($competition)
        ;

        $this->competitions
            ->expects($this->once())
            ->method('update')
            ->with($this->identicalTo($competition))
        ;

        $command = new DisableCompetition('1', new UserId(1));

        $this->service->execute($command);

        $this->assertFalse($competition->active);
    }
}
