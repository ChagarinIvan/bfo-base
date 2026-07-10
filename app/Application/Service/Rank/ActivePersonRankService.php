<?php

declare(strict_types=1);

namespace App\Application\Service\Rank;

use App\Application\Dto\Rank\RankAssembler;
use App\Application\Dto\Rank\ViewRankDto;
use App\Domain\Person\PersonRepository;
use App\Domain\ProtocolLine\ProtocolLineRepository;
use App\Domain\Rank\JuniorRankAgeValidator;
use App\Domain\Rank\JuniorThirdRankChecker;
use App\Domain\Rank\PreviousCompletedRankFiller;
use App\Domain\Rank\Rank;
use App\Domain\Rank\RankRepository;
use App\Domain\Shared\Clock;
use App\Domain\Shared\Criteria;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use function array_filter;
use function array_key_exists;
use function array_keys;
use function array_values;

final readonly class ActivePersonRankService
{
    public function __construct(
        private RankRepository $ranks,
        private JuniorThirdRankChecker $thirdRankChecker,
        private RankAssembler $assembler,
        private PreviousCompletedRankFiller $previousCompletedRankFiller,
        private Clock $clock,
        private PersonRepository $persons,
        private ProtocolLineRepository $protocolLines,
        private JuniorRankAgeValidator $ageValidator,
    ) {
    }

    public function execute(ActivePersonRank $command): ?ViewRankDto
    {
        $lastRank = $this->ranks->oneByCriteria($this->criteriaWithDate($command));
//        dump('$lastRank?->rank: '. $lastRank?->rank);

        if ($lastRank === null) {
            $lastCompletedRank = $this->ranks->oneByCriteria($this->criteriaWithoutDate($command));
//            dump('$lastCompletedRank: ' . $lastCompletedRank?->rank ?? '---');
            $lastRank = $this->fillFromPrevious($command->personId(), $lastCompletedRank, $command->date());
        }

        return $lastRank ? $this->assembler->toViewRankDto($lastRank) : null;
    }

    /**
     * Батчевый вариант execute(): вместо пары запросов на каждую персону — по одному
     * запросу на всю пачку (активные разряды, последние завершённые разряды, персоны,
     * протокольные линии). Медленный путь с заполнением предыдущих разрядов остаётся
     * per-person, но работает уже на предзагруженных данных.
     *
     * @param array<int|string> $personIds
     * @return array<int, ViewRankDto|null> map personId => dto
     */
    public function executeForMany(array $personIds, ?Carbon $date = null): array
    {
        if ($personIds === []) {
            return [];
        }

        $onDate = $date ?? $this->clock->now();

        $ranks = $this->ranks->byCriteria(new Criteria([
            'person_ids' => $personIds,
            'activated' => true,
            'date' => $onDate,
        ]));

        // сортировка запроса (finish_date desc, events.date desc) сохраняется,
        // поэтому первый разряд каждой персоны — тот же, что вернул бы oneByCriteria
        $lastRanks = [];
        foreach ($ranks as $rank) {
            /** @var Rank $rank */
            $lastRanks[$rank->person_id] ??= $rank;
        }

        $result = [];
        foreach ($this->assembler->toViewRankDtos(array_values($lastRanks)) as $dto) {
            $result[(int) $dto->personId] = $dto;
        }

        $missingIds = [];
        foreach ($personIds as $personId) {
            if (!array_key_exists((int) $personId, $result)) {
                $missingIds[] = (int) $personId;
            }
        }

        if ($missingIds === []) {
            return $result;
        }

        // последние завершённые разряды остальных персон — одним запросом
        $lastCompletedRanks = [];
        $completed = $this->ranks->byCriteria(new Criteria([
            'person_ids' => $missingIds,
            'activated' => true,
            'startDateLess' => $onDate,
        ]));
        foreach ($completed as $rank) {
            /** @var Rank $rank */
            $lastCompletedRanks[$rank->person_id] ??= $rank;
        }

        // валидатору юниорских разрядов понадобятся персоны — грузим одним запросом
        $this->ageValidator->warmUp(
            $this->persons->byCriteria(new Criteria(['ids' => $missingIds])),
            $missingIds,
        );

        // проверка на 3ю юношеский тоже работает по одной персоне — прогреваем и её
        $this->thirdRankChecker->warmUp($missingIds, $date);

        // протокольные линии за 2 года для персон вообще без разрядов — одним запросом
        // (критерии и сортировка совпадают с запросом внутри PreviousCompletedRankFiller)
        $preloadedLines = [];
        $noRankIds = array_values(array_filter(
            $missingIds,
            static fn (int $id): bool => !array_key_exists($id, $lastCompletedRanks),
        ));
        if ($noRankIds !== []) {
            $preloadedLines = $this->protocolLines->byCriteria(new Criteria(
                [
                    'personIds' => $noRankIds,
                    'dateFrom' => $onDate->clone()->addYears(-2),
                    'dateTo' => $onDate,
                    'completedRank' => true,
                    'massCompetition' => false,
                ],
                ['completedRank' => 'desc', 'eventDate' => 'asc'],
            ))->groupBy('person_id')->all();
        }

        $resolvedRanks = [];
        foreach ($missingIds as $personId) {
            $lastCompletedRank = $lastCompletedRanks[$personId] ?? null;
            $resolvedRanks[$personId] = $this->fillFromPrevious(
                $personId,
                $lastCompletedRank,
                $date,
                $lastCompletedRank === null ? ($preloadedLines[$personId] ?? Collection::empty()) : null,
            );
            $result[$personId] = null;
        }

        $resolvedRanks = array_filter($resolvedRanks);
        $dtos = $this->assembler->toViewRankDtos(array_values($resolvedRanks));
        foreach (array_keys($resolvedRanks) as $i => $personId) {
            $result[$personId] = $dtos[$i];
        }

        return $result;
    }

    public function criteriaWithDate(ActivePersonRank $command): Criteria
    {
        return new Criteria(['person_id' => $command->personId(), 'activated' => true, 'date' => $command->date() ?? $this->clock->now()]);
    }

    public function criteriaWithoutDate(ActivePersonRank $command): Criteria
    {
        return new Criteria(['person_id' => $command->personId(), 'activated' => true, 'startDateLess' => $command->date() ?? $this->clock->now()]);
    }

    /**
     * Медленный путь: восстановление разряда из предыдущих выполнений
     * (может дозаписывать разряды в базу) и проверка на 3ю юношеский.
     */
    private function fillFromPrevious(
        int $personId,
        ?Rank $lastCompletedRank,
        ?Carbon $date,
        ?Collection $preloadedProtocolLines = null,
    ): ?Rank {
        $lastRank = $this->previousCompletedRankFiller->fill($personId, $lastCompletedRank, $date, $preloadedProtocolLines);

        while ($lastRank !== null && $lastRank->finish_date->lessThan($date ?? $this->clock->now())) {
            $lastRank = $this->previousCompletedRankFiller->fill($personId, $lastRank, $date);
        }

        if ($lastRank === null) {
            $thirdJuniorRank = $this->thirdRankChecker->check($personId, $date);
            if ($thirdJuniorRank && (($date === null) || ($thirdJuniorRank->start_date < $date))) {
//                dump('add third junior rank');
                $this->ranks->add($thirdJuniorRank);
                $lastRank = $thirdJuniorRank;
            }
        }

        return $lastRank;
    }
}
