<?php

declare(strict_types=1);

namespace Tests\Domain\RankCheck\Factory;

use App\Domain\Rank\Rank;
use App\Domain\RankCheck\Factory\RankCheckRowInput;
use App\Domain\RankCheck\Factory\StandardRankCheckRowFactory;
use App\Domain\RankCheck\RankCheckPersonSnapshot;
use App\Domain\RankCheck\RankListItem;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class StandardRankCheckRowFactoryTest extends TestCase
{
    #[Test]
    public function it_builds_equal_row_from_line_and_person_snapshot(): void
    {
        $line = $this->line();
        $person = new RankCheckPersonSnapshot(17, 'Иванов Иван', 'Клуб', 'I', '2005');

        $row = new StandardRankCheckRowFactory()->create(new RankCheckRowInput(
            rankCheckId: 3,
            position: 4,
            line: $line,
            person: $person,
        ));

        $this->assertSame(3, $row->rank_check_id);
        $this->assertSame(4, $row->position);
        $this->assertSame('Иванов Иван', $row->name);
        $this->assertSame('I', $row->rank);
        $this->assertSame(17, $row->person_id);
        $this->assertTrue($row->has_person);
        $this->assertTrue($row->is_equal);
    }

    #[Test]
    public function it_builds_unmatched_row_without_person_data(): void
    {
        $row = new StandardRankCheckRowFactory()->create(new RankCheckRowInput(
            rankCheckId: 3,
            position: 1,
            line: $this->line(),
            person: null,
        ));

        $this->assertNull($row->person_id);
        $this->assertNull($row->database_name);
        $this->assertFalse($row->has_person);
        $this->assertFalse($row->is_equal);
    }

    private function line(): RankListItem
    {
        return new RankListItem(
            group: 'М18',
            name: 'Иванов Иван',
            lastname: 'Иванов',
            firstname: 'Иван',
            club: 'Клуб',
            rank: Rank::FirstRank,
            number: '12',
            year: 2005,
        );
    }
}
