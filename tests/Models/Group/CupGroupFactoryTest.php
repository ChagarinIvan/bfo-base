<?php

namespace Tests\Models\Group;

use App\Models\Group\CupGroup;
use App\Models\Group\CupGroupFactory;
use App\Models\Group\GroupAge;
use App\Models\Group\GroupMale;
use PHPUnit\Framework\TestCase;

final class CupGroupFactoryTest extends TestCase
{
    /**
     * @dataProvider provideGroupId
     * @test
     */
    public function it_creates_group(string $id, CupGroup $group): void
    {
        $this->assertEquals($group, CupGroupFactory::fromId($id));
    }

    public function provideGroupId(): iterable
    {
        yield ['M_12_', new CupGroup(GroupMale::Man, GroupAge::a12)];
        yield ['M_21_', new CupGroup(GroupMale::Man, GroupAge::a21)];
    }
}
