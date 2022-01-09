<?php

namespace App\Models\Cups;

use App\Models\CupEvent;
use App\Models\CupEventPoint;
use App\Models\Group\CupGroup;
use App\Models\Group\CupGroupFactory;
use App\Models\Group\GroupAge;
use App\Models\Group\GroupMale;
use Illuminate\Support\Collection;

/**
 * При начислении очков Кубка БФО среди юниоров очки начисляются спортсменам
 * в случае участия в группе 20 или в сильнейшей из групп 21.
 * Если спортсмен на этапе участвует в сильнейшей 21 группе при наличии 20 группы,
 * ему начисляются очки в соответствии с расчётом от лидера 21 группы.
 *
 * В случае отсутствия на этапе 20 группы,
 * очки в юниорский рейтинг рассчитываются отдельно для спортсменов
 * соответствующего возраста из результатов сильнейшей 21 группы.
 */
class JuniorCupType extends MasterCupType
{
    public function getId(): string
    {
        return CupType::JUNIORS;
    }

    public function getNameKey(): string
    {
        return 'app.cup.type.junior';
    }

    public function getGroups(): Collection
    {
        return CupGroupFactory::getAgeTypeGroups([GroupAge::a20]);
    }

    protected function getGroupProtocolLines(CupEvent $cupEvent, CupGroup $group): Collection
    {
        $year = $cupEvent->cup->year;
        $startYear = $year - $group->age ?->value ?? 0;
        $eliteGroups = $group->male === GroupMale::Man ? EliteCupType::MEN_GROUPS : EliteCupType::WOMEN_GROUPS;

        $groups = $this->groupsService->getGroups($eliteGroups);
        $groups->push($group);

        return $this->protocolLinesRepository->getCupEventProtocolLinesForPersonsCertainAge(
            $cupEvent,
            $startYear,
            $startYear,
            true,
            $groups
        );
    }

    /**
     * @param CupEvent $cupEvent
     * @param CupGroup $mainGroup
     * @return Collection //array<int, CupEventPoint>
     */
    public function calculateEvent(CupEvent $cupEvent, CupGroup $mainGroup): Collection
    {
        $results = new Collection();
        $cupEventProtocolLines = $this->getGroupProtocolLines($cupEvent, $mainGroup);
        $cupEventProtocolLines = $cupEventProtocolLines->groupBy('distance.group_id');

        foreach ($cupEventProtocolLines as $groupId => $groupProtocolLines) {
            $eventGroupResults = $this->calculateGroup($cupEvent, $groupId);
            $results = $results->merge($eventGroupResults->intersectByKeys($groupProtocolLines->keyBy('person_id')));
        }

        return $results->sortByDesc(fn (CupEventPoint $cupEventResult) => $cupEventResult->points);
    }
}
