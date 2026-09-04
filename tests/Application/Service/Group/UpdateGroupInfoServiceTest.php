<?php

declare(strict_types=1);

namespace Tests\Application\Service\Group;

use App\Application\Dto\Auth\AuthAssembler;
use App\Application\Dto\Auth\UserId;
use App\Application\Dto\Group\GroupAssembler;
use App\Application\Dto\Group\GroupDto;
use App\Application\Service\Group\Exception\FailedToUpdateGroup;
use App\Application\Service\Group\Exception\GroupNotFound;
use App\Application\Service\Group\UpdateGroupInfo;
use App\Application\Service\Group\UpdateGroupInfoService;
use App\Domain\Auth\Impression;
use App\Domain\Group\Exception\GroupAlreadyExists;
use App\Domain\Group\Group;
use App\Domain\Group\GroupInput;
use App\Domain\Group\GroupNameNormalizer;
use App\Domain\Group\GroupRepository;
use App\Domain\Group\GroupUpdater;
use App\Domain\Shared\DummyTransactional;
use App\Domain\Shared\SymbolNormalizer;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

final class UpdateGroupInfoServiceTest extends TestCase
{
    private GroupRepository&MockObject $groups;

    private GroupUpdater&MockObject $updater;

    private UpdateGroupInfoService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new UpdateGroupInfoService(
            $this->groups = $this->createMock(GroupRepository::class),
            $this->updater = $this->createMock(GroupUpdater::class),
            new GroupNameNormalizer(new SymbolNormalizer),
            new DummyTransactional,
            new GroupAssembler(new AuthAssembler),
        );
    }

    #[Test]
    public function it_fails_when_group_is_not_found(): void
    {
        $this->expectException(GroupNotFound::class);
        $this->groups->expects($this->once())->method('lockById')->with(1)->willReturn(null);
        $this->updater->expects($this->never())->method('update');

        $this->service->execute($this->command('M21'));
    }

    #[Test]
    public function it_maps_domain_duplicate_to_application_error(): void
    {
        $group = $this->groupMock();
        $this->groups->expects($this->once())->method('lockById')->with(1)->willReturn($group);
        $this->updater->expects($this->once())->method('update')->willThrowException(new GroupAlreadyExists());

        $this->expectException(FailedToUpdateGroup::class);
        $this->service->execute($this->command('M21'));
    }

    #[Test]
    public function it_updates_group_through_domain_updater(): void
    {
        $group = $this->groupMock();
        $this->groups->expects($this->once())->method('lockById')->with(1)->willReturn($group);
        $this->updater->expects($this->once())->method('update')->with($group, $this->isInstanceOf(GroupInput::class))->willReturn($group);
        $this->groups->expects($this->once())->method('update')->with($group);

        $this->service->execute($this->command('M21'));
    }

    #[Test]
    public function it_skips_update_when_group_info_is_unchanged(): void
    {
        $group = $this->groupMock('M21', 'm21');
        $this->groups->expects($this->once())->method('lockById')->with(1)->willReturn($group);
        $this->updater->expects($this->never())->method('update');
        $this->groups->expects($this->never())->method('update');

        $this->assertSame('M21', $this->service->execute($this->command('M21'))->name);
    }

    private function command(string $name): UpdateGroupInfo
    {
        $dto = new GroupDto();
        $dto->name = $name;

        return new UpdateGroupInfo('1', $dto, new UserId(1));
    }

    private function groupMock(string $name = 'M20', string $normalizedName = 'м20'): Group
    {
        $group = $this->createStub(Group::class);
        $group->method('__get')->willReturnMap([
            ['id', 1], ['name', $name], ['normalize_name', $normalizedName], ['distances_count', 0],
            ['created', new Impression(new Carbon('2026-01-01'), 1)],
            ['updated', new Impression(new Carbon('2026-01-01'), 1)],
        ]);
        $group->method('__isset')->willReturnMap([['distances_count', true]]);

        return $group;
    }
}
