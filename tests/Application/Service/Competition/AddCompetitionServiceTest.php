<?php

declare(strict_types=1);

namespace Tests\Application\Service\Competition;

use App\Application\Dto\Auth\AuthAssembler;
use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Competition\CompetitionAssembler;
use App\Application\Dto\Competition\CompetitionDto;
use App\Application\Service\Competition\AddCompetition;
use App\Application\Service\Competition\AddCompetitionService;
use App\Domain\Competition\CompetitionFactory;
use App\Domain\Competition\CompetitionInput;
use App\Domain\Competition\CompetitionRepository;
use App\Models\Competition;
use Carbon\Carbon;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class AddCompetitionServiceTest extends TestCase
{
    private AddCompetitionService $service;

    private CompetitionFactory&MockObject $factory;

    private CompetitionRepository&MockObject $competitions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AddCompetitionService(
            $this->factory = $this->createMock(CompetitionFactory::class),
            $this->competitions = $this->createMock(CompetitionRepository::class),
            new CompetitionAssembler(new AuthAssembler()),
        );
    }

    /** @test */
    public function it_creates_competition(): void
    {
        $input = new CompetitionInput(
            name: 'test competition',
            description: 'test competition description',
            from: Carbon::parse('2023-01-01'),
            to: Carbon::parse('2023-01-02'),
            userId: 1,
        );

        /** @var Competition $competition */
        $competition = Competition::factory()->makeOne();

        $this->factory
            ->expects($this->once())
            ->method('create')
            ->with($this->equalTo($input))
            ->willReturn($competition)
        ;

        $this->competitions
            ->expects($this->once())
            ->method('add')
            ->with($this->identicalTo($competition))
        ;

        $dto = new CompetitionDto();
        $dto->name = 'test competition';
        $dto->description = 'test competition description';
        $dto->from = '2023-01-01';
        $dto->to = '2023-01-02';

        $command = new AddCompetition($dto, new UserId(1));
        $competitionDto = $this->service->execute($command);

        $this->assertEquals($competition->id, $competitionDto->id);
    }
}
