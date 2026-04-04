<?php

declare(strict_types=1);

namespace Tests\Application\Service\Competition;

use App\Application\Dto\Auth\AuthAssembler;
use App\Application\Dto\Competition\CompetitionAssembler;
use App\Application\Dto\Competition\CompetitionSearchDto;
use App\Application\Dto\Competition\ViewCompetitionDto;
use App\Application\Service\Competition\ListCompetitions;
use App\Application\Service\Competition\ListCompetitionsService;
use App\Domain\Competition\Competition;
use App\Domain\Competition\CompetitionRepository;
use App\Domain\Shared\Criteria;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class ListCompetitionsServiceTest extends TestCase
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

    #[Test]
    public function it_gets_list_of_competitions(): void
    {
        /** @var Competition[] $competitions */
        $competitions = Competition::factory(count: 2)->make();

        $this->competitions
            ->expects($this->once())
            ->method('byCriteria')
            ->with(new Criteria(['year' => '2021']))
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
