<?php

declare(strict_types=1);

namespace Tests\Services;

use App\Domain\Event\Protocol;
use App\Domain\Group\GroupRepository;
use App\Services\ParserService;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class ParserServiceTest extends TestCase
{
    #[Test]
    public function it_trims_club_names_from_protocol_parsers(): void
    {
        $groups = $this->createMock(GroupRepository::class);
        $groups->expects($this->once())->method('all')->willReturn(new Collection);
        $parser = new ParserService($groups);

        $lines = $parser->parse(new Protocol($this->protocol(), 'html'));

        $this->assertSame('Тэст клуб', $lines->sole()['club']);
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
