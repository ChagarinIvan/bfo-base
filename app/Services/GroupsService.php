<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\CupEvent;
use App\Models\Group;
use App\Repositories\GroupsRepository;
use Illuminate\Support\Collection;
use RuntimeException;
use function in_array;

class GroupsService
{
    public function __construct(private readonly GroupsRepository $groupsRepository)
    {
    }

    /**
     * @param CupEvent $cupEvent
     * @return Collection|Group[]
     */
    public function getCupEventGroups(CupEvent $cupEvent): Collection
    {
        return $this->groupsRepository->getEventGroups($cupEvent->event_id);
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

    /**
     * @param string[] $groupsNames
     * @return Collection|Group[]
     */
    public function getGroups(array $groupsNames): Collection
    {
        $groups = new Collection();
        foreach ($groupsNames as $groupName) {
            $group = $this->groupsRepository->searchGroup($groupName);
            if ($group) {
                $groups->push($group);
            }
        }

        return $groups;
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
     * @param array $groupIdList
     * @return Collection
     */
    public function getAllGroupsWithout(array $groupIdList = []): Collection
    {
        $groups = $this->groupsRepository->getAll();
        return $groups->filter(static fn (Group $group) => !in_array($group->id, $groupIdList, true));
    }
}
