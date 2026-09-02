<?php

declare(strict_types=1);

namespace App\Domain\Person;

use App\Domain\Rank\Rank;
use Carbon\Carbon;
use function array_filter;
use function array_last;
use function array_map;
use function array_values;
use function count;
use function usort;

final class RankCalculator
{
    private const int MAX_JUNIOR_AGE = 18;

    /** @param list<RankFact> $rankFacts */
    public function calculate(array $rankFacts, Person $person, Carbon $on): PersonRankState
    {
        $history = $this->buildHistory($this->calculationFacts($rankFacts, $person->birthday));

        return new PersonRankState(
            current: $this->currentRank($history, $on),
            history: $history,
        );
    }

    /** @param list<RankFact> $rankFacts
     * @return list<RankFact>
     */
    private function calculationFacts(array $rankFacts, ?Carbon $birthday): array
    {
        $facts = array_values(array_filter(
            array: $this->addJuniorThirdRankFacts($rankFacts, $birthday),
            callback: fn (RankFact $fact): bool => $this->isEligibleForCalculation($fact, $birthday),
        ));

        return $this->sortFactsByAchievement($facts);
    }

    private function isEligibleForCalculation(RankFact $fact, ?Carbon $birthday): bool
    {
        return $fact->rank !== Rank::WithoutRank
            && (!$fact->rank->isJunior() || $this->isJuniorOnAchievementDate($birthday, $fact->achievedOn));
    }

    private function isJuniorOnAchievementDate(?Carbon $birthday, Carbon $achievedOn): bool
    {
        return $birthday === null
            || $achievedOn->year - $birthday->year <= self::MAX_JUNIOR_AGE;
    }

    /**
     * @param list<RankFact> $facts
     * @return list<RankFact>
     */
    private function sortFactsByAchievement(array $facts): array
    {
        usort($facts, static fn (RankFact $left, RankFact $right): int => $left->achievedOn <=> $right->achievedOn ?: $left->protocolLineId <=> $right->protocolLineId);

        return $facts;
    }

    /** @param list<RankFact> $facts
     * @return list<PersonRankHistory>
     */
    private function buildHistory(array $facts): array
    {
        $history = [];
        foreach ($facts as $fact) {
            $history = $this->appendHistoryFact($history, $fact);
        }

        return $history;
    }

    /**
     * @param list<PersonRankHistory> $history
     * @return list<PersonRankHistory>
     */
    private function appendHistoryFact(array $history, RankFact $fact): array
    {
        $activatedOn = $fact->activatedOn ?? ($fact->rank->isAutomaticallyActivated() ? $fact->achievedOn : null);
        $startedOn = $activatedOn ?? $fact->achievedOn;
        $finishedOn = $activatedOn?->copy()->addYears(2);
        $previous = $history === [] ? null : array_last($history);
        $history = $this->extendActivePeriods($history, $fact, $startedOn, $finishedOn);

        $history[] = new PersonRankHistory(
            protocolLineId: $fact->protocolLineId,
            distanceId: $fact->distanceId,
            eventId: $fact->eventId,
            competitionId: $fact->competitionId,
            rank: $fact->rank,
            changeType: $this->changeType($fact, $previous),
            achievedOn: $fact->achievedOn,
            activatedOn: $activatedOn,
            startedOn: $startedOn,
            finishedOn: $finishedOn,
        );

        return $history;
    }

    private function changeType(RankFact $fact, ?PersonRankHistory $previous): RankChangeType
    {
        return match (true) {
            $previous === null => RankChangeType::Completion,
            $previous->rank === $fact->rank => RankChangeType::Extension,
            $previous->rank->value > $fact->rank->value => RankChangeType::Downgrade,
            default => RankChangeType::Promotion,
        };
    }

    /** @param list<PersonRankHistory> $history */
    private function currentRank(array $history, Carbon $on): PersonRank
    {
        $active = array_values(array_filter($history, fn (PersonRankHistory $entry): bool => $this->isActiveOn($entry, $on)));
        $sorted = $this->sortActiveHistory($active);
        $current = $sorted[0] ?? null;

        return new PersonRank(
            rank: $current === null ? Rank::WithoutRank : $current->rank,
            startedOn: $current?->startedOn,
            activatedOn: $current?->activatedOn,
            finishedOn: $current?->finishedOn,
        );
    }

    private function isActiveOn(PersonRankHistory $history, Carbon $on): bool
    {
        return $history->activatedOn !== null
            && $history->startedOn <= $on
            && ($history->finishedOn === null || $history->finishedOn >= $on);
    }

    /**
     * @param list<PersonRankHistory> $history
     * @return list<PersonRankHistory>
     */
    private function sortActiveHistory(array $history): array
    {
        usort($history, static fn (PersonRankHistory $left, PersonRankHistory $right): int => $right->rank->value <=> $left->rank->value
            ?: $right->startedOn <=> $left->startedOn
            ?: $right->protocolLineId <=> $left->protocolLineId);

        return $history;
    }

    /**
     * A repeated qualification prolongs the still-active period created by a
     * previous qualification of the same rank. This mirrors the legacy
     * updater, including its exclusion of rows from the same event.
     *
     * @param list<PersonRankHistory> $history
     * @return list<PersonRankHistory>
     */
    private function extendActivePeriods(array $history, RankFact $achievement, Carbon $startedOn, ?Carbon $finishedOn): array
    {
        if ($finishedOn === null) {
            return $history;
        }

        return array_map(
            function (PersonRankHistory $entry) use ($achievement, $startedOn, $finishedOn): PersonRankHistory {
                if (!$this->shouldExtendPeriod($entry, $achievement, $startedOn)) {
                    return $entry;
                }

                return new PersonRankHistory(
                    protocolLineId: $entry->protocolLineId,
                    distanceId: $entry->distanceId,
                    eventId: $entry->eventId,
                    competitionId: $entry->competitionId,
                    rank: $entry->rank,
                    changeType: $entry->changeType,
                    achievedOn: $entry->achievedOn,
                    activatedOn: $entry->activatedOn,
                    startedOn: $entry->startedOn,
                    finishedOn: $finishedOn,
                );
            },
            $history,
        );
    }

    private function shouldExtendPeriod(PersonRankHistory $history, RankFact $fact, Carbon $startedOn): bool
    {
        return $history->rank === $fact->rank
            && $history->eventId !== $fact->eventId
            && $history->startedOn < $startedOn
            && $history->finishedOn !== null
            && $history->finishedOn > $startedOn;
    }

    /**
     * @param list<RankFact> $facts
     * @return list<RankFact>
     */
    private function addJuniorThirdRankFacts(array $facts, ?Carbon $birthday): array
    {
        if ($birthday === null) {
            return $facts;
        }

        $eligible = [];
        foreach ($facts as $fact) {
            if (!$this->isJuniorOnAchievementDate($birthday, $fact->achievedOn)) {
                continue;
            }
            $eligible[$fact->achievedOn->year][] = $fact;
        }

        foreach ($eligible as $year => $yearAchievements) {
            $yearAchievements = $this->sortFactsByAchievement($yearAchievements);

            if (count($yearAchievements) < 3 || array_filter($facts, static fn (RankFact $item): bool => $item->rank === Rank::JuniorThirdRank && $item->achievedOn->year === $year) !== []) {
                continue;
            }

            $source = $yearAchievements[2];

            $facts[] = new RankFact(
                protocolLineId: $source->protocolLineId,
                distanceId: $source->distanceId,
                eventId: $source->eventId,
                competitionId: $source->competitionId,
                rank: Rank::JuniorThirdRank,
                achievedOn: $source->achievedOn,
                activatedOn: $source->achievedOn,
            );
        }

        return $facts;
    }
}
