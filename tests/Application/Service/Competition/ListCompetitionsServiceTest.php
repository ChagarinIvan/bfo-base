<?php

declare(strict_types=1);

namespace Application\Service\Competition;

use App\Application\Dto\Auth\AuthAssembler;
use App\Application\Dto\Competition\CompetitionAssembler;
use App\Application\Dto\Competition\CompetitionSearchDto;
use App\Application\Dto\Competition\ViewCompetitionDto;
use App\Application\Dto\Event\EventAssembler;
use App\Application\Dto\Event\EventSearchDto;
use App\Application\Dto\Event\ViewEventDto;
use App\Application\Service\Competition\ListCompetitions;
use App\Application\Service\Competition\ListCompetitionsService;
use App\Application\Service\Event\ListEvents;
use App\Application\Service\Event\ListEventsService;
use App\Domain\Competition\Competition;
use App\Domain\Competition\CompetitionRepository;
use App\Domain\Event\Event;
use App\Domain\Event\EventRepository;
use App\Domain\Shared\Criteria;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

class ListCompetitionsServiceTest extends TestCase
{
    private ListCompetitionsService $service;

    private CompetitionRepository&MockObject $competitions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new ListCompetitionsService(
            $this->competitions = $this->createMock(CompetitionRepository::class),
            new CompetitionAssembler(new AuthAssembler),
        );
    }

    /** @test */
    public function it_gets_list_of_competitions(): void
    {
        /** @var Competition[] $competitions */
        $competitions = Competition::factory(count: 2)->make();

        $this->competitions
            ->expects($this->once())
            ->method('byCriteria')
            ->with($this->equalTo(new Criteria(['year' => '2021'])))
            ->willReturn($competitions)
        ;

        $dto = new CompetitionSearchDto('2021');

        $command = new ListCompetitions($dto);
        $result = $this->service->execute($command);

        $this->assertIsList($result);
        $this->assertContainsOnlyInstancesOf(ViewCompetitionDto::class, $result);
        $this->assertEquals($competitions[1]->id, $result[1]->id);
    }
}
