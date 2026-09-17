<?php

declare(strict_types=1);

namespace Tests\Application\Service\RankCheck;

use App\Application\Dto\RankCheck\RankCheckRowAssembler;
use App\Application\Service\RankCheck\Exception\RankCheckNotFound;
use App\Application\Service\RankCheck\ListRankCheckRows;
use App\Application\Service\RankCheck\ListRankCheckRowsService;
use App\Domain\RankCheck\RankCheck;
use App\Domain\RankCheck\RankCheckRepository;
use App\Domain\RankCheck\RankCheckRow;
use App\Domain\RankCheck\RankCheckRowRepository;
use App\Domain\Shared\Criteria;
use App\Domain\Shared\Pagination\Slice;
use Pagerfanta\Adapter\ArrayAdapter;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class ListRankCheckRowsServiceTest extends TestCase
{
    private MockObject&RankCheckRepository $checks;

    private MockObject&RankCheckRowRepository $rows;

    private ListRankCheckRowsService $service;

    private static function matchesCriteria(Criteria $criteria): bool
    {
        return $criteria->params() === ['rankCheckId' => 17];
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new ListRankCheckRowsService(
            checks: $this->checks = $this->createMock(RankCheckRepository::class),
            rows: $this->rows = $this->createMock(RankCheckRowRepository::class),
            assembler: new RankCheckRowAssembler(),
        );
    }

    #[Test]
    public function it_lists_rows_by_rank_check_id_and_assembles_them(): void
    {
        $check = $this->createStub(RankCheck::class);
        $firstRow = $this->createStub(RankCheckRow::class);
        $firstRow->method('__get')->willReturnMap([
            ['position', 1],
            ['group', 'M18'],
            ['name', 'Иванов Иван'],
            ['club', 'Клуб'],
            ['rank', 'I'],
            ['number', '12'],
            ['year', '2005'],
            ['person_id', 8],
            ['database_name', 'Иванов Иван'],
            ['database_club', 'Клуб'],
            ['database_rank', 'I'],
            ['database_year', '2005'],
            ['has_person', true],
            ['is_equal', true],
        ]);

        $this->checks
            ->expects($this->once())
            ->method('byId')
            ->with(17)
            ->willReturn($check)
        ;

        $this->rows
            ->expects($this->once())
            ->method('paginateRows')
            ->with($this->callback(static fn(Criteria $criteria): bool => self::matchesCriteria($criteria)))
            ->willReturn(new Slice(new ArrayAdapter([$firstRow])))
        ;

        $result = $this->service->execute(new ListRankCheckRows(17));
        $items = $result->items();

        $this->assertSame('Иванов Иван', $items[0]->name);
        $this->assertTrue($items[0]->isEqual);
    }

    #[Test]
    public function it_throws_when_rank_check_does_not_exist(): void
    {
        $this->checks
            ->expects($this->once())
            ->method('byId')
            ->with(17)
            ->willReturn(null)
        ;

        $this->rows->expects($this->never())->method('paginateRows');

        $this->expectException(RankCheckNotFound::class);

        $this->service->execute(new ListRankCheckRows(17));
    }
}
