<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\Group\Group;
use App\Repositories\GroupsRepository;
use Illuminate\Support\Collection;
use RuntimeException;
use function in_array;

final readonly class GroupsService
{
    public function __construct(private GroupsRepository $groupsRepository)
    {
    }

    public function getGroupsList(array $with = []): Collection
    {
        return $this->groupsRepository->getAll($with);
    }

    public function getGroup(int $groupId): Group
    {
        $group = $this->groupsRepository->getGroup($groupId);
        if ($group) {
            return $group;
        }
        throw new RuntimeException('Wrong group id.');
    }

    public function deleteGroup(Group $group): void
    {
        foreach ($group->distances as $distance) {
            $distance->protocolLines()->delete();
        }
        $group->distances()->delete();
        $group->delete();
    }

    /**
     * @param int[] $groupIdList
     */
    public function getAllGroupsWithout(array $groupIdList = []): Collection
    {
        return $this
            ->groupsRepository
            ->getAll()
            ->filter(static fn (Group $group): bool => !in_array($group->id, $groupIdList, true))
        ;
    }
}
