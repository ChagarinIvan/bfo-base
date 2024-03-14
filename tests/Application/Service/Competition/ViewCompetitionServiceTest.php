<?php

declare(strict_types=1);

namespace Tests\Application\Service\Competition;

use App\Application\Dto\Auth\AuthAssembler;
use App\Application\Dto\Competition\CompetitionAssembler;
use App\Application\Dto\Competition\ViewCompetitionDto;
use App\Application\Service\Competition\Exception\CompetitionNotFound;
use App\Application\Service\Competition\ViewCompetition;
use App\Application\Service\Competition\ViewCompetitionService;
use App\Domain\Competition\CompetitionRepository;
use App\Models\Competition;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class ViewCompetitionServiceTest extends TestCase
{
    private ViewCompetitionService $service;

    private CompetitionRepository&MockObject $competitions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new ViewCompetitionService(
            $this->competitions = $this->createMock(CompetitionRepository::class),
            new CompetitionAssembler(new AuthAssembler),
        );
    }

    /** @test */
    public function it_fails_when_competition_not_found(): void
    {
        $this->expectException(CompetitionNotFound::class);

        $this->competitions
            ->expects($this->once())
            ->method('byId')
            ->with($this->equalTo(1))
            ->willReturn(null)
        ;

        $command = new ViewCompetition('1');
        $this->service->execute($command);
    }

    /** @test */
    public function it_shows_competition(): void
    {
        /** @var Competition $competition */
        $competition = Competition::factory()->makeOne();

        $this->competitions
            ->expects($this->once())
            ->method('byId')
            ->with($this->equalTo(1))
            ->willReturn($competition)
        ;

        $command = new ViewCompetition('1');
        $result = $this->service->execute($command);

        $this->assertInstanceOf(ViewCompetitionDto::class, $result);
        $this->assertEquals($competition->id, $result->id);
    }
}
