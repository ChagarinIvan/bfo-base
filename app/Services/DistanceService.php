<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\Cup\CupEvent\CupEvent;
use App\Domain\Distance\Distance;
use App\Domain\Event\Event;
use App\Repositories\DistanceRepository;
use Illuminate\Support\Collection;
use function implode;
use function in_array;
use function str_contains;

class DistanceService
{
    /**
     * Дистанции, предзагруженные одним запросом на все события кубка,
     * сгруппированные по event_id. Пока прелоад не вызван — null и все
     * методы ходят в репозиторий (см. preloadEventDistances()).
     *
     * @var array<int, Collection<int, Distance>>|null
     */
    private ?array $preloadedEventDistances = null;

    public function __construct(private readonly DistanceRepository $distanceRepository)
    {
    }

    /**
     * Загружает дистанции всех событий (с группами) одним запросом, чтобы
     * расчёт кубка не делал отдельные запросы на каждое событие (n+1).
     *
     * @param int[] $eventIds
     */
    public function preloadEventDistances(array $eventIds): void
    {
        $this->preloadedEventDistances = $this->distanceRepository
            ->getEventsDistances($eventIds)
            ->groupBy('event_id')
            ->all()
        ;
    }

    public function getCupEventDistancesByGroups(CupEvent $cupEvent, Collection $groups, bool $withEquals = false): Collection
    {
        if ($this->preloadedEventDistances !== null) {
            $distances = $this
                ->preloadedDistances($cupEvent->event_id)
                ->filter(static fn (Distance $distance): bool => $groups->contains($distance->group_id))
                ->values()
            ;

            if (!$withEquals) {
                return $distances;
            }

            $result = Collection::empty();

            foreach ($distances as $distance) {
                $result->add($distance);
                $result->push(...$this->getEqualDistances($distance)->values());
            }

            return $result->unique();
        }

        return $this->distanceRepository->getCupEventDistancesByGroups($cupEvent, $groups, $withEquals);
    }

    /**
     * @param string[] $groupNames
     */
    public function findDistance(array $groupNames, int $eventId): ?Distance
    {
        // like-паттерны (%) резолвим только запросом
        if ($this->preloadedEventDistances !== null && !str_contains(implode('', $groupNames), '%')) {
            return $this
                ->preloadedDistances($eventId)
                ->first(static fn (Distance $distance): bool => in_array($distance->group->name, $groupNames, true))
            ;
        }

        return $this->distanceRepository->findDistance($groupNames, $eventId);
    }

    public function deleteEventDistances(Event $event): void
    {
        $event->distances()->delete();
    }

    public function getEventGroupDistance(Event $event, int $groupId): ?Distance
    {
        return $this->distanceRepository->getEventGroupDistance($event->id, $groupId);
    }

    /**
     * @return Collection|Distance[]
     */
    public function getEqualDistances(Distance $mainDistance): array|Collection
    {
        if ($this->preloadedEventDistances !== null) {
            return $this
                ->preloadedDistances($mainDistance->event_id)
                ->filter(static fn (Distance $distance): bool => $distance->id !== $mainDistance->id && $distance->equal($mainDistance))
                ->values()
            ;
        }

        return $this->distanceRepository->getEqualDistances($mainDistance);
    }

    public function updateDistanceGroup(Distance $distance, int $groupId): Distance
    {
        $distance->group_id = $groupId;
        $distance->save();
        return $distance;
    }

    public function byId(int $id): ?Distance
    {
        return $this->distanceRepository->byId($id);
    }

    /** @return Collection<int, Distance> */
    private function preloadedDistances(int $eventId): Collection
    {
        return $this->preloadedEventDistances[$eventId] ?? Collection::empty();
    }
}
