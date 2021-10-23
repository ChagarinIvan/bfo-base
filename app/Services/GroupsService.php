<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CupEvent;
use App\Models\Event;
use App\Models\Group;
use App\Repositories\GroupsRepository;
use Illuminate\Support\Collection;

class GroupsService
{
    private GroupsRepository $groupsRepository;

    public function __construct(GroupsRepository $groupsRepository)
    {
        $this->groupsRepository = $groupsRepository;
    }

    public function deleteEventDistances(Event $event): void
    {
        $event->distances()->delete();
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

    public function getGroup(int $groupId): ?Group
    {
        return $this->groupsRepository->getGroup($groupId);
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
        $group->delete();
    }
}
