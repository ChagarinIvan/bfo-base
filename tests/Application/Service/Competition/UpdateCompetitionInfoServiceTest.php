<?php

declare(strict_types=1);

namespace Tests\Application\Service\Competition;

use App\Application\Dto\Auth\AuthAssembler;
use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Competition\CompetitionAssembler;
use App\Application\Dto\Competition\CompetitionDto;
use App\Application\Service\Competition\Exception\CompetitionNotFound;
use App\Application\Service\Competition\UpdateCompetitionInfo;
use App\Application\Service\Competition\UpdateCompetitionInfoService;
use App\Domain\Competition\CompetitionRepository;
use App\Domain\Shared\DummyTransactional;
use App\Domain\Shared\FrozenClock;
use App\Models\Competition;
use Carbon\Carbon;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

class UpdateCompetitionInfoServiceTest extends TestCase
{
    private UpdateCompetitionInfoService $service;

    private CompetitionRepository&MockObject $competitions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new UpdateCompetitionInfoService(
            $this->competitions = $this->createMock(CompetitionRepository::class),
            new FrozenClock(Carbon::parse('2023-04-01')),
            new CompetitionAssembler(new AuthAssembler),
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

        $info = new CompetitionDto();
        $info->name = 'test competition';
        $info->description = 'test competition';
        $info->from = '2023-01-01';
        $info->to = '2023-01-02';

        $command = new UpdateCompetitionInfo('1', $info, new UserId(1));
        $this->service->execute($command);
    }

    /** @test */
    public function it_updates_competition_info(): void
    {
        $competition = Competition::factory()->makeOne();

        $this->competitions
            ->expects($this->once())
            ->method('lockById')
            ->with($this->equalTo(1))
            ->willReturn($competition)
        ;

        $this->competitions->expects($this->once())->method('update');

        $info = new CompetitionDto();
        $info->name = 'test competition new';
        $info->description = 'test competition';
        $info->from = '2023-02-02';
        $info->to = '2023-02-03';

        $command = new UpdateCompetitionInfo('1', $info, new UserId(1));
        $competition = $this->service->execute($command);

        $this->assertEquals('test competition new', $competition->name);
        $this->assertEquals('test competition', $competition->description);
        $this->assertEquals('2023-02-02', $competition->from);
        $this->assertEquals('2023-02-03', $competition->to);
        $this->assertEquals('2023-04-01T00:00:00+00:00', $competition->updated->at);
    }
}
