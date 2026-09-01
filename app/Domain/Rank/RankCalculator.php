<?php

declare(strict_types=1);

namespace App\Domain\Rank;

use Carbon\CarbonImmutable;
use function array_filter;
use function array_last;
use function array_values;
use function count;
use function usort;

final class RankCalculator
{
    /** @param list<RankAchievement> $achievements */
    public function calculate(array $achievements, CarbonImmutable $on): CalculatedPersonRank
    {
        $achievements = $this->addJuniorThirdRankAchievements($achievements);
        $facts = array_values(array_filter(
            $achievements,
            static fn (RankAchievement $achievement): bool => !$achievement->massCompetition
                && !$achievement->outOfCompetition
                && $achievement->rank !== Rank::WithoutRank
                && (!$achievement->rank->isJunior()
                    || ($achievement->birthday !== null
                        && $achievement->achievedOn->year - $achievement->birthday->year <= Rank::MAX_JUNIOR_AGE)),
        ));
        usort($facts, static fn (RankAchievement $left, RankAchievement $right): int => $left->achievedOn <=> $right->achievedOn ?: $left->protocolLineId <=> $right->protocolLineId);

        $history = [];
        foreach ($facts as $fact) {
            $activatedOn = $fact->activatedOn ?? ($fact->rank->isAutomaticallyActivated() ? $fact->achievedOn : null);
            $startedOn = $activatedOn ?? $fact->achievedOn;
            $finishedOn = $activatedOn?->addYears(2);
            $previous = $history === [] ? null : array_last($history);
            $changeType = $fact->rank === Rank::JuniorThirdRank
                ? 'junior_third'
                : match (true) {
                    $previous === null => 'completion',
                    $previous->rank === $fact->rank => 'extension',
                    $previous->rank->strength() > $fact->rank->strength() => 'downgrade',
                    default => 'promotion',
                };
            $history[] = new PersonRankHistory(
                protocolLineId: $fact->protocolLineId,
                distanceId: $fact->distanceId,
                eventId: $fact->eventId,
                competitionId: $fact->competitionId,
                rank: $fact->rank,
                changeType: $changeType,
                achievedOn: $fact->achievedOn,
                activatedOn: $activatedOn,
                startedOn: $startedOn,
                finishedOn: $finishedOn,
            );
        }

        $active = array_values(array_filter($history, static fn (PersonRankHistory $entry): bool => $entry->activatedOn !== null && $entry->startedOn <= $on && ($entry->finishedOn === null || $entry->finishedOn >= $on)));
        usort($active, static fn (PersonRankHistory $left, PersonRankHistory $right): int => $right->rank->strength() <=> $left->rank->strength()
            ?: $right->startedOn <=> $left->startedOn
            ?: $right->protocolLineId <=> $left->protocolLineId);
        $current = $active[0] ?? null;

        return new CalculatedPersonRank(
            currentRank: $current === null ? Rank::WithoutRank : $current->rank,
            startedOn: $current?->startedOn,
            activatedOn: $current?->activatedOn,
            finishedOn: $current?->finishedOn,
            history: $history,
        );
    }

    /** @param list<RankAchievement> $achievements
     * @return list<RankAchievement>
     */
    private function addJuniorThirdRankAchievements(array $achievements): array
    {
        $eligible = [];
        foreach ($achievements as $achievement) {
            if ($achievement->massCompetition || $achievement->outOfCompetition || !$achievement->hasResult || $achievement->birthday === null) {
                continue;
            }
            if ($achievement->achievedOn->year - $achievement->birthday->year > Rank::MAX_JUNIOR_AGE) {
                continue;
            }
            $eligible[$achievement->achievedOn->year][] = $achievement;
        }

        foreach ($eligible as $year => $yearAchievements) {
            usort($yearAchievements, static fn (RankAchievement $left, RankAchievement $right): int => $left->achievedOn <=> $right->achievedOn ?: $left->protocolLineId <=> $right->protocolLineId);
            if (count($yearAchievements) < 3 || array_filter($achievements, static fn (RankAchievement $item): bool => $item->rank === Rank::JuniorThirdRank && $item->achievedOn->year === $year) !== []) {
                continue;
            }
            $source = $yearAchievements[2];
            $achievements[] = new RankAchievement(
                personId: $source->personId,
                protocolLineId: $source->protocolLineId,
                distanceId: $source->distanceId,
                eventId: $source->eventId,
                competitionId: $source->competitionId,
                rank: Rank::JuniorThirdRank,
                achievedOn: $source->achievedOn,
                activatedOn: $source->achievedOn,
                birthday: $source->birthday,
            );
        }

        return $achievements;
    }
}
