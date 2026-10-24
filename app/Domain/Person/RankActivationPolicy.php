<?php

declare(strict_types=1);

namespace App\Domain\Person;

use function usort;

final readonly class RankActivationPolicy
{
    /** @param list<RankFact> $facts
     * @return list<RankFact>
     */
    public function apply(array $facts): array
    {
        usort($facts, static fn (RankFact $left, RankFact $right): int => $left->achievedOn <=> $right->achievedOn
            ?: ($right->activatedOn !== null) <=> ($left->activatedOn !== null)
            ?: $left->protocolLineId <=> $right->protocolLineId);
        $confirmed = [];
        foreach ($facts as $index => $fact) {
            if (!$fact->rank->isAutomaticallyActivated() && $fact->activatedOn === null) {
                foreach ($confirmed as $prior) {
                    if ($prior->protocolLineId !== $fact->protocolLineId
                        && $prior->rank->value >= $fact->rank->value
                        && $prior->activatedOn <= $fact->achievedOn) {
                        $fact = new RankFact(
                            $fact->protocolLineId,
                            $fact->distanceId,
                            $fact->eventId,
                            $fact->competitionId,
                            $fact->rank,
                            $fact->achievedOn,
                            $fact->achievedOn->copy(),
                            $fact->delayedActivation,
                        );
                        $facts[$index] = $fact;
                        break;
                    }
                }
            }
            if ($fact->activatedOn !== null) {
                $confirmed[] = $fact;
            }
        }

        return $facts;
    }
}
