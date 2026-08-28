<?php

declare(strict_types=1);

namespace Tests\Domain\Cup\Group;

use App\Domain\Cup\Group\GroupAge;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use function count;

final class GroupAgeTest extends TestCase
{
    #[Test]
    public function it_walks_the_full_age_ladder_forward(): void
    {
        $ladder = [
            GroupAge::a12, GroupAge::a14, GroupAge::a16, GroupAge::a18, GroupAge::a20,
            GroupAge::a21, GroupAge::a35, GroupAge::a40, GroupAge::a45, GroupAge::a50,
            GroupAge::a55, GroupAge::a60, GroupAge::a65, GroupAge::a70, GroupAge::a75,
            GroupAge::a80,
        ];

        for ($i = 0; $i < count($ladder) - 1; $i++) {
            $this->assertSame($ladder[$i + 1], $ladder[$i]->next(), "next() для {$ladder[$i]->name} должен быть {$ladder[$i + 1]->name}");
        }
    }

    #[Test]
    public function it_walks_the_full_age_ladder_backward(): void
    {
        $ladder = [
            GroupAge::a16, GroupAge::a18, GroupAge::a20, GroupAge::a21, GroupAge::a35,
            GroupAge::a40, GroupAge::a45, GroupAge::a50, GroupAge::a55, GroupAge::a60,
            GroupAge::a65, GroupAge::a70, GroupAge::a75, GroupAge::a80,
        ];
        $counter = count($ladder);

        for ($i = 1; $i < $counter; $i++) {
            $this->assertSame($ladder[$i - 1], $ladder[$i]->prev(), "prev() для {$ladder[$i]->name} должен быть {$ladder[$i - 1]->name}");
        }
    }

    #[Test]
    public function forward_ladder_saturates_at_the_oldest_group(): void
    {
        $this->assertSame(GroupAge::a80, GroupAge::a80->next());
    }

    #[Test]
    public function backward_ladder_saturates_at_the_youngest_group(): void
    {
        // ниже a16 переход не задан → падение к младшей a12
        $this->assertSame(GroupAge::a12, GroupAge::a14->prev());
        $this->assertSame(GroupAge::a12, GroupAge::a12->prev());
    }

    #[Test]
    public function to_string_returns_numeric_age_as_string(): void
    {
        $this->assertSame('12', GroupAge::a12->toString());
        $this->assertSame('21', GroupAge::a21->toString());
        $this->assertSame('80', GroupAge::a80->toString());
    }
}
