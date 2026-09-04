<?php

declare(strict_types=1);

namespace Tests\Application\Service\Group;

use App\Application\Dto\Auth\UserId;
use App\Application\Service\Group\Exception\CannotMergeSameGroup;
use App\Application\Service\Group\Exception\GroupNotFound;
use App\Application\Service\Group\MergeGroups;
use App\Application\Service\Group\MergeGroupsService;
use App\Domain\Auth\Impression;
use App\Domain\Distance\DistanceMover;
use App\Domain\Group\Group;
use App\Domain\Group\GroupRepository;
use App\Domain\Shared\DummyTransactional;
use App\Domain\Shared\FrozenClock;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class MergeGroupsServiceTest extends TestCase
{
    private GroupRepository&MockObject $groups;

    private DistanceMover&MockObject $distances;

    private MergeGroupsService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new MergeGroupsService(
            $this->groups = $this->createMock(GroupRepository::class),
            $this->distances = $this->createMock(DistanceMover::class),
            new FrozenClock(new Carbon('2026-09-04')),
            new DummyTransactional,
        );
    }

    #[Test]
    public function it_rejects_equal_source_and_target(): void
    {
        $this->groups->expects($this->never())->method('lockById');
        $this->distances->expects($this->never())->method('moveFromGroupToGroup');

        $this->expectException(CannotMergeSameGroup::class);
        $this->service->execute($this->command('1', '1'));
    }

    #[Test]
    public function it_fails_when_source_group_is_not_found(): void
    {
        $this->groups->expects($this->once())->method('lockById')->with(1)->willReturn(null);
        $this->distances->expects($this->never())->method('moveFromGroupToGroup');

        $this->expectException(GroupNotFound::class);
        $this->service->execute($this->command('1', '2'));
    }

    #[Test]
    public function it_fails_when_target_group_is_not_found(): void
    {
        $source = $this->groupStub(1);
        $this->groups->expects($this->exactly(2))->method('lockById')->willReturnMap([[1, $source], [2, null]]);
        $this->distances->expects($this->never())->method('moveFromGroupToGroup');

        $this->expectException(GroupNotFound::class);
        $this->service->execute($this->command('1', '2'));
    }

    #[Test]
    public function it_moves_distances_and_soft_deletes_source_group(): void
    {
        $source = $this->group(1);
        $target = $this->groupStub(2);
        $this->groups->expects($this->exactly(2))->method('lockById')->willReturnMap([[1, $source], [2, $target]]);
        $this->distances->expects($this->once())->method('moveFromGroupToGroup')->with(1, 2);
        $source->expects($this->once())->method('disable')->with($this->isInstanceOf(Impression::class));
        $this->groups->expects($this->once())->method('update')->with($source);

        $this->service->execute($this->command('1', '2'));
    }

    private function command(string $sourceId, string $targetId): MergeGroups
    {
        return new MergeGroups($sourceId, $targetId, new UserId(5));
    }

    private function group(int $id): Group&MockObject
    {
        $group = $this->createMock(Group::class);
        $group->method('__get')->willReturnCallback(static fn (string $property): ?int => $property === 'id' ? $id : null);

        return $group;
    }

    private function groupStub(int $id): Group
    {
        $group = $this->createStub(Group::class);
        $group->method('__get')->willReturnCallback(static fn (string $property): ?int => $property === 'id' ? $id : null);

        return $group;
    }
}
