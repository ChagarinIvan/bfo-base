<?php

declare(strict_types=1);

namespace Tests\Domain\Cup\Table;

use App\Domain\Cup\Cup;
use App\Domain\Cup\CupEvent\CupEventPoint;
use App\Domain\Cup\Group\CupGroupFactory;
use App\Domain\Cup\Table\CupTable;
use App\Domain\Cup\Table\StandardCupTableService;
use App\Domain\ProtocolLine\ProtocolLine;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class StandardCupTableServiceTest extends TestCase
{
    #[Test]
    public function it_builds_stage_rows_and_totals_from_calculated_points(): void
    {
        $group = CupGroupFactory::fromId('M_0_');
        $events = new Collection();
        $protocolLine = $this->protocolLineStub(100, 42, '  Doe', 'Jane ', 1990, 'Club', 501);
        $unassignedLine = $this->protocolLineStub(101, null, 'Unknown', 'Runner', 0, '', 502);
        $points = [
            new CupEventPoint(11, $protocolLine, 80),
            new CupEventPoint(12, $protocolLine, 0),
            new CupEventPoint(13, $protocolLine, 50),
        ];
        $cup = $this->createMock(Cup::class);
        $cup
            ->expects($this->once())
            ->method('calculateGroupEvents')
            ->with($group, $events)
            ->willReturn([
                42 => $points,
                '' => [new CupEventPoint(11, $unassignedLine, 100)],
            ])
        ;
        $cup->setAttribute('events_count', 2);

        $table = (new StandardCupTableService())->build($cup, $events, $group);

        $this->assertInstanceOf(CupTable::class, $table);
        $this->assertSame([], $table->stages);
        $this->assertCount(1, $table->rows);

        $row = $table->rows[0];
        $this->assertSame(1, $row->place);
        $this->assertSame('42', $row->personId);
        $this->assertSame('Doe Jane', $row->personName);
        $this->assertSame(1990, $row->personYear);
        $this->assertSame('Club', $row->clubName);
        $this->assertSame('80', $row->totalPoints);
        $this->assertSame('80', $row->averagePoints);
        $this->assertTrue($row->stages['11']->counted);
        $this->assertSame(11, $row->stages['11']->stageId);
        $this->assertTrue($row->stages['12']->counted);
        $this->assertFalse($row->stages['13']->counted);
    }

    private function protocolLineStub(
        int $id,
        ?int $personId,
        string $lastname,
        string $firstname,
        int $year,
        string $club,
        int $distanceId,
    ): ProtocolLine {
        $protocolLine = $this->getMockBuilder(ProtocolLine::class)
            ->onlyMethods(['getAttribute'])
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $protocolLine
            ->method('getAttribute')
            ->willReturnMap([
                ['id', $id],
                ['person_id', $personId],
                ['lastname', $lastname],
                ['firstname', $firstname],
                ['year', $year],
                ['club', $club],
                ['distance_id', $distanceId],
            ])
        ;

        return $protocolLine;
    }
}
