<?php

declare(strict_types=1);

namespace Tests\Application\Service\Cup;

use App\Application\Dto\Cup\CupExportAssembler;
use App\Application\Dto\Cup\CupTableAssembler;
use App\Application\Service\Cup\ExportCupTable;
use App\Application\Service\Cup\ExportCupTableService;
use App\Domain\Cup\Cup;
use App\Domain\Cup\CupEvent\CupEventRepository;
use App\Domain\Cup\CupRepository;
use App\Domain\Cup\Group\CupGroup;
use App\Domain\Cup\Group\GroupMale;
use App\Domain\Cup\Table\CupTable;
use App\Domain\Cup\Table\CupTableBuilder;
use App\Domain\Shared\Criteria;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ExportCupTableServiceTest extends TestCase
{
    #[Test]
    public function it_queries_stages_once_and_builds_every_group(): void
    {
        $group = new CupGroup(GroupMale::Man);
        $cup = $this->createMock(Cup::class);
        $cup->expects($this->atLeast(2))->method('__get')->willReturnMap([['name', 'Cup'], ['id', 101]]);
        $cup->method('groups')->willReturn([$group]);
        $cups = $this->createMock(CupRepository::class);
        $cups->expects($this->once())->method('byId')->with(101)->willReturn($cup);
        $events = Collection::empty();
        $cupEvents = $this->createMock(CupEventRepository::class);
        $cupEvents->expects($this->once())->method('byCriteria')->willReturnCallback(function (Criteria $criteria, object $resources) use ($events): Collection {
            $this->assertSame(101, $criteria->param('cupId'));
            $this->assertTrue($resources->withCup);
            $this->assertTrue($resources->withEvent);

            return $events;
        });
        $table = new CupTable([], []);
        $builder = $this->createMock(CupTableBuilder::class);
        $builder->expects($this->once())->method('build')->with($cup, $events, $group)->willReturn($table);
        $assembler = new CupExportAssembler(new CupTableAssembler());

        $result = new ExportCupTableService($cups, $cupEvents, $builder, $assembler)->execute(new ExportCupTable('101'));

        $this->assertSame('Cup', $result->cupName);
        $this->assertSame(101, $result->cupId);
        $this->assertSame($group->name(), $result->sections[0]->groupName);
    }
}
