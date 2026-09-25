<?php

declare(strict_types=1);

namespace Tests\Application\Service\Cup;

use App\Application\Dto\Cup\CupTableAssembler;
use App\Application\Dto\Cup\ViewCupTableDto;
use App\Application\Service\Cup\Exception\CupNotFound;
use App\Application\Service\Cup\ViewCupTable;
use App\Application\Service\Cup\ViewCupTableService;
use App\Domain\Cup\Cup;
use App\Domain\Cup\CupEvent\CupEventRepository;
use App\Domain\Cup\CupEvent\CupEventResources;
use App\Domain\Cup\CupRepository;
use App\Domain\Cup\Group\CupGroupFactory;
use App\Domain\Cup\Table\CupTable;
use App\Domain\Cup\Table\CupTableService;
use App\Domain\Shared\Criteria;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class ViewCupTableServiceTest extends TestCase
{
    private ViewCupTableService $service;

    private CupRepository&MockObject $cups;

    private CupEventRepository&MockObject $cupEvents;

    private CupTableService&MockObject $table;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new ViewCupTableService(
            $this->cups = $this->createMock(CupRepository::class),
            $this->cupEvents = $this->createMock(CupEventRepository::class),
            $this->table = $this->createMock(CupTableService::class),
            new CupTableAssembler(),
        );
    }

    #[Test]
    public function it_builds_a_view_dto_through_the_cup_table_service(): void
    {
        $cup = $this->createStub(Cup::class);
        $events = new Collection();
        $group = CupGroupFactory::fromId('M_0_');
        $table = new CupTable([], []);

        $this->cups
            ->expects($this->once())
            ->method('byId')
            ->with(42)
            ->willReturn($cup)
        ;
        $this->cupEvents
            ->expects($this->once())
            ->method('byCriteria')
            ->with($this->callback(static fn (Criteria $criteria): bool =>
                $criteria->params() === ['cupId' => 42]
                && $criteria->sorting() === ['event.date' => 'asc']
            ), new CupEventResources(withCup: true, withEvent: true))
            ->willReturn($events)
        ;
        $this->table
            ->expects($this->once())
            ->method('build')
            ->with($cup, $events, $group)
            ->willReturn($table)
        ;

        $result = $this->service->execute(new ViewCupTable('42', $group));

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

        $this->service->execute(new ViewCupTable('42', CupGroupFactory::fromId('M_0_')));
    }
}
