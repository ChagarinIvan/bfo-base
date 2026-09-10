<?php

declare(strict_types=1);

namespace Tests\Application\Service\ProtocolLine;

use App\Application\Dto\Auth\UserId;
use App\Application\Service\Person\RebuildPersonRanksService;
use App\Application\Service\PersonPrompt\ChangePersonPromptService;
use App\Application\Service\ProtocolLine\SetPersonToProtocolLines;
use App\Application\Service\ProtocolLine\SetPersonToProtocolLinesService;
use App\Domain\Auth\Impression;
use App\Domain\Person\PersonRepository;
use App\Domain\Person\RankCalculator;
use App\Domain\Person\RankFactsCollector;
use App\Domain\PersonPrompt\Factory\PersonPromptFactory;
use App\Domain\PersonPrompt\PersonPrompt;
use App\Domain\PersonPrompt\PersonPromptRepository;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\ProtocolLine\ProtocolLineRepository;
use App\Domain\Shared\DummyTransactional;
use App\Domain\Shared\FrozenClock;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class AssignPersonOnProtocolLinesServiceTest extends TestCase
{
    #[Test]
    public function it_assigns_equal_lines_and_updates_prompts_and_ranks(): void
    {
        /** @var ProtocolLine&MockObject $source */
        $source = $this->lineStub('ivanou-jan-2001', 10);
        /** @var ProtocolLine&MockObject $equal */
        $equal = $this->lineStub('ivanou-jan-2001', 11);
        $lines = $this->createMock(ProtocolLineRepository::class);
        $lines->expects($this->once())->method('byCriteria')->willReturn(new Collection([$source, $equal]));
        $lines->expects($this->exactly(2))->method('update');
        $source->expects($this->once())->method('setPerson')->with(42, $this->isInstanceOf(Impression::class));
        $equal->expects($this->once())->method('setPerson')->with(42, $this->isInstanceOf(Impression::class));

        $promptRepository = $this->createMock(PersonPromptRepository::class);
        $promptRepository->expects($this->once())->method('byCriteria')->willReturn(new Collection());
        $promptRepository->expects($this->once())->method('add');
        $promptFactory = $this->createMock(PersonPromptFactory::class);
        $promptFactory->expects($this->once())->method('create')->willReturn($this->createStub(PersonPrompt::class));
        $persons = $this->createMock(PersonRepository::class);
        $persons->expects($this->atLeastOnce())->method('lockById')->willReturn(null);
        $clock = new FrozenClock(Carbon::parse('2026-09-09'));

        new SetPersonToProtocolLinesService(
            $lines,
            new ChangePersonPromptService($promptRepository, $promptFactory, $clock),
            new RebuildPersonRanksService($persons, $this->createStub(RankFactsCollector::class), new RankCalculator(), $clock, new DummyTransactional()),
            $clock,
        )->execute(new SetPersonToProtocolLines(
            'ivanou-jan-2001',
            42,
            new UserId(7),
        ));
    }

    /** @return ProtocolLine&MockObject */
    private function lineStub(string $preparedLine, ?int $personId): ProtocolLine
    {
        $line = $this->createMock(ProtocolLine::class);
        $line->method('__get')->willReturnMap([
            ['prepared_line', $preparedLine],
            ['person_id', $personId],
        ]);

        return $line;
    }
}
