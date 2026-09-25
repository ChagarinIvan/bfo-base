<?php

declare(strict_types=1);

namespace Tests\Application\Service\Cup;

use App\Application\Dto\Cup\CupTableAssembler;
use App\Application\Dto\Cup\CupTableSearchDto;
use App\Application\Dto\Cup\ViewCupTableDto;
use App\Application\Service\Cup\Exception\CupNotFound;
use App\Application\Service\Cup\Exception\UnsupportedCupGroup;
use App\Application\Service\Cup\ViewCupTable;
use App\Application\Service\Cup\ViewCupTableService;
use App\Domain\Cup\Cup;
use App\Domain\Cup\CupEvent\CupEventRepository;
use App\Domain\Cup\CupEvent\CupEventResources;
use App\Domain\Cup\CupRepository;
use App\Domain\Cup\Group\CupGroup;
use App\Domain\Cup\Group\CupGroupFactory;
use App\Domain\Cup\Table\CupTable;
use App\Domain\Cup\Table\CupTableBuilder;
use App\Domain\Cup\Table\CupTableRow;
use App\Domain\Shared\Criteria;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;
use function array_map;

final class ViewCupTableServiceTest extends TestCase
{
    private ViewCupTableService $service;

    private CupRepository&MockObject $cups;

    private CupEventRepository&MockObject $cupEvents;

    private CupTableBuilder&MockObject $table;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new ViewCupTableService(
            $this->cups = $this->createMock(CupRepository::class),
            $this->cupEvents = $this->createMock(CupEventRepository::class),
            $this->table = $this->createMock(CupTableBuilder::class),
            new CupTableAssembler(),
        );
    }

    #[Test]
    public function it_builds_a_view_dto_through_the_cup_table_service(): void
    {
        $events = new Collection();
        $group = CupGroupFactory::fromId('M_0_');
        $cup = $this->cupStubWithGroups([$group]);
        $table = new CupTable([], []);

        $this->cups->expects($this->once())->method('byId')->with(42)->willReturn($cup);
        $this->cupEvents
            ->expects($this->once())
            ->method('byCriteria')
            ->with(new Criteria(['cupId' => 42], ['event.date' => 'asc']), new CupEventResources(withCup: true, withEvent: true))
            ->willReturn($events)
        ;
        $this->table
            ->expects($this->once())
            ->method('build')
            ->with($cup, $events, $group)
            ->willReturn($table)
        ;

        $result = $this->service->execute(new ViewCupTable('42', 'M_0_', (new CupTableSearchDto())->fromArray([])));

        $this->assertInstanceOf(ViewCupTableDto::class, $result);
        $this->assertSame([], $result->stages);
        $this->assertSame([], $result->rows);
    }

    #[Test]
    public function it_fails_when_the_cup_does_not_exist(): void
    {
        $this->expectException(CupNotFound::class);

        $this->cups
            ->expects($this->once())
            ->method('byId')
            ->with(42)
            ->willReturn(null)
        ;
        $this->cupEvents->expects($this->never())->method('byCriteria');
        $this->table->expects($this->never())->method('build');

        $this->service->execute(new ViewCupTable('42', 'M_0_', (new CupTableSearchDto())->fromArray([])));
    }

    #[Test]
    public function it_filters_cached_rows_by_name(): void
    {
        $events = new Collection();
        $group = CupGroupFactory::fromId('M_0_');
        $cup = $this->cupStubWithGroups([$group]);
        $table = new CupTable([], [
            $this->row(1, 42, 'John Doe'),
            $this->row(2, 43, 'Jane Doe'),
            $this->row(3, 44, 'John Smith'),
        ]);

        $this->cups->expects($this->once())->method('byId')->with(42)->willReturn($cup);
        $this->cupEvents
            ->expects($this->once())
            ->method('byCriteria')
            ->willReturn($events)
        ;
        $this->table
            ->expects($this->once())
            ->method('build')
            ->willReturn($table)
        ;
        $result = $this->service->execute(new ViewCupTable('42', 'M_0_', (new CupTableSearchDto())->fromArray(['name' => 'john'])));
        $rows = $result->rows;

        $this->assertCount(2, $rows);
        $this->assertSame(['John Doe', 'John Smith'], array_map(static fn ($row): string => $row->personName, $rows));
        $this->assertSame([1, 3], array_map(static fn ($row): int => $row->place, $rows));
    }

    #[Test]
    public function it_rejects_a_group_not_supported_by_the_cup(): void
    {
        $cup = $this->cupStubWithGroups([CupGroupFactory::fromId('M_0_')]);
        $this->cups->expects($this->once())->method('byId')->with(42)->willReturn($cup);
        $this->cupEvents->expects($this->never())->method('byCriteria');
        $this->table->expects($this->never())->method('build');

        $this->expectException(UnsupportedCupGroup::class);
        $this->service->execute(new ViewCupTable('42', 'M_12_', (new CupTableSearchDto())->fromArray([])));
    }

    private function row(int $place, int $personId, string $name): CupTableRow
    {
        return new CupTableRow(
            place: $place,
            personId: (string) $personId,
            personName: $name,
            personYear: 1990,
            clubName: 'Club',
            stages: [],
            totalPoints: '100',
            averagePoints: '100',
        );
    }

    /** @param list<CupGroup> $groups */
    private function cupStubWithGroups(array $groups): Cup
    {
        $cup = $this->getMockBuilder(Cup::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['groups'])
            ->getMock()
        ;
        $cup->id = 42;
        $cup->method('groups')->willReturn($groups);

        return $cup;
    }
}
