<?php

declare(strict_types=1);

namespace Tests\Domain\RankCheck;

use App\Domain\Rank\Rank;
use App\Domain\RankCheck\Exception\ProcessError;
use App\Domain\RankCheck\Factory\RankCheckRowFactory;
use App\Domain\RankCheck\Factory\RankCheckRowInput;
use App\Domain\RankCheck\RankCheck;
use App\Domain\RankCheck\RankCheckPersonMatcher;
use App\Domain\RankCheck\RankCheckPersonSnapshot;
use App\Domain\RankCheck\RankCheckPersonSnapshotReader;
use App\Domain\RankCheck\RankCheckRow;
use App\Domain\RankCheck\RankCheckRowRepository;
use App\Domain\RankCheck\RankListItem;
use App\Domain\RankCheck\RankListParser;
use App\Domain\RankCheck\StandardRankCheckProcessor;
use App\Domain\Shared\IdentLineGenerator;
use App\Domain\Shared\Storage;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;
use Tests\TestCase;

final class StandardRankCheckProcessorTest extends TestCase
{
    private MockObject&RankListParser $parser;

    private MockObject&RankCheckPersonMatcher $matcher;

    private MockObject&Storage $storage;

    private IdentLineGenerator&MockObject $identLineGenerator;

    private MockObject&RankCheckPersonSnapshotReader $people;

    private MockObject&RankCheckRowFactory $rowFactory;

    private MockObject&RankCheckRowRepository $rows;

    private StandardRankCheckProcessor $processor;

    private static function matchesRowInput(RankCheckRowInput $input): bool
    {
        return $input->rankCheckId === 7;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->processor = new StandardRankCheckProcessor(
            parser: $this->parser = $this->createMock(RankListParser::class),
            matcher: $this->matcher = $this->createMock(RankCheckPersonMatcher::class),
            storage: $this->storage = $this->createMock(Storage::class),
            identLineGenerator: $this->identLineGenerator = $this->createMock(IdentLineGenerator::class),
            people: $this->people = $this->createMock(RankCheckPersonSnapshotReader::class),
            rowFactory: $this->rowFactory = $this->createMock(RankCheckRowFactory::class),
            rows: $this->rows = $this->createMock(RankCheckRowRepository::class),
        );
    }

    #[Test]
    public function it_matches_lines_and_adds_created_rows(): void
    {
        $lines = [
            $this->line('Иванов Иван', 'Иванов', 'Иван', 2005),
            $this->line('Петров Пётр', 'Петров', 'Пётр', 2006),
        ];
        $check = $this->check();
        $person = new RankCheckPersonSnapshot(10, 'Иванов Иван', 'Клуб', 'I', '2005');
        $firstRow = $this->createStub(RankCheckRow::class);
        $secondRow = $this->createStub(RankCheckRow::class);

        $this->storage->expects($this->once())->method('get')->with('rank-check.csv')->willReturn('content');
        $this->parser->expects($this->once())->method('parse')->with('content', 'csv')->willReturn($lines);
        $this->identLineGenerator
            ->expects($this->exactly(2))
            ->method('generate')
            ->willReturnMap([
                ['Иванов', 'Иван', 2005, 'ivanov_ivan_2005'],
                ['Петров', 'Пётр', 2006, 'petrov_petr_2006'],
            ])
        ;
        $this->matcher
            ->expects($this->once())
            ->method('match')
            ->with(['ivanov_ivan_2005', 'petrov_petr_2006'])
            ->willReturn(['ivanov_ivan_2005' => 10])
        ;
        $this->people
            ->expects($this->once())
            ->method('read')
            ->with([10])
            ->willReturn([10 => $person])
        ;
        $this->rowFactory
            ->expects($this->exactly(2))
            ->method('create')
            ->with($this->callback(static fn(RankCheckRowInput $input): bool => self::matchesRowInput($input)))
            ->willReturnOnConsecutiveCalls($firstRow, $secondRow)
        ;
        $addedRows = [];
        $this->rows
            ->expects($this->exactly(2))
            ->method('add')
            ->willReturnCallback(static function (RankCheckRow $row) use (&$addedRows): void {
                $addedRows[] = $row;
            })
        ;

        $this->processor->process($check);

        $this->assertSame([$firstRow, $secondRow], $addedRows);
    }

    #[Test]
    public function it_wraps_processing_exceptions(): void
    {
        $this->parser->expects($this->never())->method('parse');
        $this->identLineGenerator->expects($this->never())->method('generate');
        $this->matcher->expects($this->never())->method('match');
        $this->people->expects($this->never())->method('read');
        $this->rowFactory->expects($this->never())->method('create');
        $this->rows->expects($this->never())->method('add');
        $this->storage
            ->expects($this->once())
            ->method('get')
            ->willThrowException(new RuntimeException('storage failed'))
        ;

        $this->expectException(ProcessError::class);
        $this->expectExceptionMessageIsOrContains('Не удалось обработать список разрядов.');

        $this->processor->process($this->check());
    }

    private function check(): RankCheck
    {
        $check = $this->createStub(RankCheck::class);
        $check->method('__get')->willReturnMap([
            ['id', 7],
            ['source_path', 'rank-check.csv'],
        ]);

        return $check;
    }

    private function line(string $name, string $lastname, string $firstname, int $year): RankListItem
    {
        return new RankListItem(
            group: 'M18',
            name: $name,
            lastname: $lastname,
            firstname: $firstname,
            club: 'Клуб',
            rank: Rank::FirstRank,
            number: '1',
            year: $year,
        );
    }
}
