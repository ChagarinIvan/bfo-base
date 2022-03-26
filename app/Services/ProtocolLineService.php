<?php

namespace App\Services;

use App\Models\Distance;
use App\Models\Event;
use App\Models\Group;
use App\Models\ProtocolLine;
use App\Models\Rank;
use App\Models\Year;
use App\Repositories\GroupsRepository;
use App\Repositories\ProtocolLinesRepository;
use Illuminate\Support\Collection;

class ProtocolLineService
{
    public function __construct(
        private ProtocolLinesRepository $protocolLinesRepository,
        private GroupsRepository $groupsRepository
    ) {}

    /**
     * Коллекция сырых данных линий протокола, из каждой
     * формирует модель записи протокола
     * определяем группу
     * формируем идентификационную строку
     * заполняем разряд
     *
     * @param int $eventId
     * @param Collection $lineList
     * @return Collection
     */
    public function fillProtocolLines(int $eventId, Collection $lineList): Collection
    {
        return $lineList->transform(function (array $lineData) use ($eventId) {
            $protocolLine = new ProtocolLine($lineData);
            $groupName = str_replace(' ', '', $lineData['group']);
            $group = $this->groupsRepository->searchGroup($groupName);

            if ($group === null) {
                $group = new Group();
                $group->name = $groupName;
                $group = $this->groupsRepository->storeGroup($group);
            }

            $distance = $this->findDistance($group->id, $eventId, (int)($lineData['distance']['length'] ?? 0), (int)($lineData['distance']['points'] ?? 0));
            $protocolLine->fillProtocolLine($distance->id);
            $protocolLine->save();
            return $protocolLine;
        });
    }

    private function findDistance(int $groupId, int $eventId, int $length, int $points): Distance
    {
        $distances = Distance::whereGroupId($groupId)
            ->whereEventId($eventId)
            ->whereLength($length)
            ->wherePoints($points)
            ->get();

        if ($distances->count() === 0) {
            $distance = new Distance();
            $distance->group_id = $groupId;
            $distance->event_id = $eventId;
            $distance->length = $length;
            $distance->points = $points;
            $distance->save();
        } else {
            $distance = $distances->first();
        }

        return $distance;
    }

    public function getProtocolLineIdForRank(Rank $rank): int
    {
        return $this->protocolLinesRepository->getLineForPersonOnEvent($rank->person_id, $rank->event_id);
    }

    public function getProtocolLineWithEvent(int $id): ProtocolLine
    {
        return $this->protocolLinesRepository->getProtocolLine($id, ['distance.event']);
    }

    public function getPersonProtocolLines(int $personId, Year $year): Collection
    {
        return $this->protocolLinesRepository->getProtocolLines($personId, $year);
    }

    /**
     * @param Collection $linesIds
     * @return Collection|ProtocolLine[]
     */
    public function getProtocolLinesInListWithoutPerson(Collection $linesIds): Collection
    {
        return ProtocolLine::whereIn('id', $linesIds)
            ->whereNull('person_id')
            ->get();
    }

    public function deleteEventLines(Event $event): void
    {
        $event->protocolLines()->delete();
    }

    public function fastIdent(Collection $linesIds): void
    {
        $this->protocolLinesRepository->identByEqualPreparedLine($linesIds);
        $this->protocolLinesRepository->identByEqualPersonPrompt($linesIds);
    }
}
