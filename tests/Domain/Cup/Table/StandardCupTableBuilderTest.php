<?php

declare(strict_types=1);

namespace Tests\Domain\Cup\Table;

use App\Domain\Cup\Cup;
use App\Domain\Cup\CupEvent\CupEventPoint;
use App\Domain\Cup\Group\CupGroupFactory;
use App\Domain\Cup\Table\CupTable;
use App\Domain\Cup\Table\StandardCupTableBuilder;
use App\Domain\ProtocolLine\ProtocolLine;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use function array_values;

final class StandardCupTableBuilderTest extends TestCase
{
    #[Test]
    public function it_builds_stage_rows_and_totals_from_calculated_points(): void
    {
        $group = CupGroupFactory::fromId('M_0_');
        $events = new Collection();
        $protocolLine = $this->protocolLineStub(100, 42, '  Doe', 'Jane ', 1990, 'Club', 501);
        $unassignedLine = $this->protocolLineStub(101, null, 'Unknown', 'Runner', 0, '', 502);
        $points = [
            0 => new CupEventPoint(11, $protocolLine, 80),
            2 => new CupEventPoint(13, $protocolLine, 50),
            1 => new CupEventPoint(12, $protocolLine, 0),
        ];
        $cup = $this->getMockBuilder(Cup::class)
            ->onlyMethods(['calculateGroupEvents'])
            ->getMock()
        ;
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

        $table = new StandardCupTableBuilder()->build($cup, $events, $group);

        $this->assertInstanceOf(CupTable::class, $table);
        $this->assertSame([], $table->stages);
        $this->assertCount(1, $table->rows);

        $row = $table->rows[0];
        $this->assertSame(1, $row->place);
        $this->assertSame('42', $row->personId);
        $this->assertSame('Doe Jane', $row->personName);
        $this->assertSame(1990, $row->personYear);
        $this->assertSame('Club', $row->clubName);
        $this->assertSame('130', $row->totalPoints);
        $this->assertSame('65', $row->averagePoints);
        $cells = array_values($row->stages);
        $this->assertTrue($cells[0]->counted);
        $this->assertSame(11, $cells[0]->stageId);
        $this->assertTrue($cells[1]->counted);
        $this->assertSame(13, $cells[1]->stageId);
        $this->assertFalse($cells[2]->counted);
        $this->assertSame(12, $cells[2]->stageId);
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
