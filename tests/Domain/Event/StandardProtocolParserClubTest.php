<?php

declare(strict_types=1);

namespace Tests\Domain\Event;

use App\Domain\Event\Event;
use App\Domain\Event\ProtocolPathResolver;
use App\Domain\Event\StandardProtocolParser;
use App\Domain\Group\GroupNameNormalizer;
use App\Domain\Group\GroupRepository;
use App\Domain\Rank\RankNormalizer;
use App\Domain\Shared\StandardIdentLineGenerator;
use App\Domain\Shared\StandardNameNormalizer;
use App\Domain\Shared\Storage;
use App\Domain\Shared\SymbolNormalizer;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class StandardProtocolParserClubTest extends TestCase
{
    #[Test]
    public function it_trims_club_names_from_protocol_parsers(): void
    {
        $event = $this->createMock(Event::class);
        $event->expects($this->exactly(2))->method('__get')->with('file')->willReturn('protocol.html');
        $storage = $this->createMock(Storage::class);
        $storage->expects($this->once())->method('get')->with('protocol.html')->willReturn($this->protocol());
        $groups = $this->createMock(GroupRepository::class);
        $groups->expects($this->once())->method('all')->willReturn(new Collection());
        $parser = new StandardProtocolParser(
            $storage,
            new ProtocolPathResolver(),
            $groups,
            new RankNormalizer(),
            new StandardIdentLineGenerator(new StandardNameNormalizer()),
            new GroupNameNormalizer(new SymbolNormalizer()),
        );

        $lines = $parser->parse($event);

        $this->assertSame('Тэст клуб', $lines[0]->club);
    }

    private function protocol(): string
    {
        return <<<'HTML'
<div class="sportorg-table"></div>
<script>
var race = {"persons":[{"id":1,"group_id":1,"organization_id":1,"year":2000,"is_out_of_competition":false,"bib":1,"qual":1,"surname":"Тэст","name":"Спартсмен"}],"courses":[{"id":1,"length":1000,"controls":[1,2]}],"organizations":[{"id":1,"name":"  Тэст клуб  "}],"groups":[{"id":1,"name":"М21","course_id":1}],"results":[{"person_id":1,"result":"00:10:00","place":1,"assigned_rank":1,"status_comment":""}]};
var Qualification = {1:'I'};
</script>
HTML;
    }
}
