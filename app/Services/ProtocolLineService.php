<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Distance;
use App\Models\Group;
use App\Models\ProtocolLine;
use App\Models\Rank;
use App\Repositories\ProtocolLinesRepository;
use Illuminate\Support\Collection;

class ProtocolLineService
{
    private ProtocolLinesRepository $protocolLinesRepository;

    public function __construct(ProtocolLinesRepository $protocolLinesRepository)
    {
        $this->protocolLinesRepository = $protocolLinesRepository;
    }

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
        $groups = Group::all();

        return $lineList->transform(function (array $lineData) use ($groups, $eventId) {
            $protocolLine = new ProtocolLine($lineData);
            $groupName = str_replace(' ', '', $lineData['group']);
            /** @var Group $group */
            $group = $groups->firstWhere('name', $groupName);
            if ($group === null) {
                throw new \RuntimeException('Wrong group '.$lineData['group']);
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
}
