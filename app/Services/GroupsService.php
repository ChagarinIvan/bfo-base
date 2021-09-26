<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CupEvent;
use App\Models\Event;
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

    public function getCupEventGroups(CupEvent $cupEvent): Collection
    {
        return $this->groupsRepository->getEventGroups($cupEvent->event_id);
    }
}
