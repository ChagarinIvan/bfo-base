<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\Distance\Distance;
use App\Domain\Event\Event;
use App\Domain\Group\Group;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\Rank\RankNormalizer;
use App\Repositories\GroupsRepository;
use App\Repositories\ProtocolLinesRepository;
use Illuminate\Support\Collection;
use RuntimeException;
use function str_replace;

class ProtocolLineService
{
    public function __construct(
        private readonly ProtocolLinesRepository $protocolLinesRepository,
        private readonly GroupsRepository $groupsRepository,
        private readonly RankNormalizer $rankNormalizer,
    ) {
    }

    /**
     * Коллекция сырых данных линий протокола, из каждой
     * формирует модель записи протокола
     * определяем группу
     * формируем идентификационную строку
     * заполняем разряд
     */
    public function fillProtocolLines(int $eventId, Collection $lineList): Collection
    {
        return $lineList->transform(function (array $lineData) use ($eventId): ProtocolLine {
            $protocolLine = new ProtocolLine($lineData);

            $groupName = str_replace(' ', '', $lineData['group']);
            $group = $this->groupsRepository->searchGroup($groupName);

            if ($group === null) {
                $group = new Group();
                $group->name = $groupName;
                $group = $this->groupsRepository->storeGroup($group);
            }

            $distance = $this->findDistance($group->id, $eventId, (int)($lineData['distance']['length'] ?? 0), (int)($lineData['distance']['points'] ?? 0));
            $protocolLine->fillProtocolLine(
                $distance->id,
                $this->rankNormalizer->normalize($protocolLine->complete_rank)?->label() ?? '',
            );

            $protocolLine->save();

            $rank = $this->rankNormalizer->normalize($protocolLine->complete_rank);
            $protocolLine->activate_rank = $rank?->isAutomaticallyActivated()
                ? $protocolLine->event->date
                : null;
            $protocolLine->save();

            return $protocolLine;
        });
    }

    public function getProtocolLine(int $id): ProtocolLine
    {
        $protocolLine = $this->protocolLinesRepository->byId($id);
        if ($protocolLine instanceof ProtocolLine) {
            return $protocolLine;
        }
        throw new RuntimeException('Wrong protocolLine id.');
    }

    public function getPersonProtocolLines(int $personId): Collection
    {
        return $this->protocolLinesRepository->getProtocolLines($personId);
    }

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

    /** @return list<int> */
    public function personIdsForEvent(Event $event): array
    {
        return $event->protocolLines()
            ->whereNotNull('person_id')
            ->distinct()
            ->pluck('person_id')
            ->map(static fn (int $personId): int => $personId)
            ->all();
    }

    public function fastIdent(Collection $linesIds): void
    {
        $this->protocolLinesRepository->identByEqualPreparedLine($linesIds);
        $this->protocolLinesRepository->identByEqualPersonPrompt($linesIds);
    }

    public function getEqualLines(string $line): Collection
    {
        return ProtocolLine::wherePreparedLine($line)->get();
    }

    public function reSetPerson(Collection $lines, int $personId): void
    {
        foreach ($lines as $line) {
            /** @var ProtocolLine $line */
            $line->person_id = $personId;
            $line->save();
        }
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
}
