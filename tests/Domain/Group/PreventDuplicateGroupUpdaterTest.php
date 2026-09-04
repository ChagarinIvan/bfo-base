<?php

declare(strict_types=1);

namespace Tests\Domain\Group;

use App\Domain\Group\Exception\GroupAlreadyExists;
use App\Domain\Group\Group;
use App\Domain\Group\GroupInfo;
use App\Domain\Group\GroupInput;
use App\Domain\Group\GroupRepository;
use App\Domain\Group\GroupUpdater;
use App\Domain\Group\PreventDuplicateGroupUpdater;
use App\Domain\Shared\Criteria;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class PreventDuplicateGroupUpdaterTest extends TestCase
{
    private GroupRepository&MockObject $groups;

    private GroupUpdater&MockObject $decorated;

    private PreventDuplicateGroupUpdater $updater;

    protected function setUp(): void
    {
        parent::setUp();
        $this->updater = new PreventDuplicateGroupUpdater(
            $this->decorated = $this->createMock(GroupUpdater::class),
            $this->groups = $this->createMock(GroupRepository::class),
        );
    }

    #[Test]
    public function it_rejects_name_owned_by_another_group(): void
    {
        $group = $this->group(1);
        $this->groups->expects($this->once())->method('oneByCriteria')->with(new Criteria(['normalizedName' => 'м21']))->willReturn($this->group(2));
        $this->decorated->expects($this->never())->method('update');

        $this->expectException(GroupAlreadyExists::class);
        $this->updater->update($group, $this->input());
    }

    #[Test]
    public function it_allows_current_group_and_delegates_update(): void
    {
        $group = $this->group(1);
        $this->groups->expects($this->once())->method('oneByCriteria')->with(new Criteria(['normalizedName' => 'м21']))->willReturn($group);
        $this->decorated->expects($this->once())->method('update')->with($group, $this->isInstanceOf(GroupInput::class))->willReturn($group);

        $this->assertSame($group, $this->updater->update($group, $this->input()));
    }

    private function input(): GroupInput
    {
        return new GroupInput(new GroupInfo('M21', 'м21'), 1);
    }

    private function group(int $id): Group
    {
        $group = $this->createStub(Group::class);
        $group->method('__get')->willReturnCallback(static fn (string $property): ?int => $property === 'id' ? $id : null);

        return $group;
    }
}
