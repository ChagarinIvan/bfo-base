<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ProtocolLine;
use App\Models\Rank;

class RankService
{
    public const RANKS_POWER = [
        Rank::WITHOUT_RANK => 0,
        Rank::UNIOR_THIRD_RANK => 1,
        Rank::UNIOR_SECOND_RANK => 2,
        Rank::UNIOR_FIRST_RANK => 3,
        Rank::THIRD_RANK => 4,
        Rank::SECOND_RANK => 5,
        Rank::FIRST_RANK => 6,
        Rank::SMC_RANK => 7,
        Rank::SM_RANK => 8,
        Rank::WSM_RANK => 9,
    ];

    public function fillRank(ProtocolLine $protocolLine): void
    {
        if (!Rank::validateRank($protocolLine->complete_rank)) {
            return;
        }

        $event = $protocolLine->event;

        $ranks = Rank::with(['event'])
            ->wherePersonId($protocolLine->person_id)
            ->where('start_date', '<=', $event->date)
            ->where('finish_date', '>=', $event->date)
            ->get()
            ->sortBy('event.date');

        /** @var Rank $lastRank */
        $lastRank = $ranks->last();
        if ($lastRank) {
            if ($lastRank->rank === $protocolLine->complete_rank) {
                $lastRank = $lastRank->replicate();
                $lastRank->event_id = $event->id;
                $lastRank->finish_date = $event->date->clone()->addYears(2);
            } elseif (self::RANKS_POWER[$protocolLine->complete_rank] > self::RANKS_POWER[$lastRank->rank]) {
                $lastRank->finish_date = $event->date->clone()->addDays(-1);
                $lastRank = $this->createNewRank($protocolLine);
            }
        } else {
            $lastRank = $this->createNewRank($protocolLine);
        }
        $lastRank->save();
    }

    private function createNewRank(ProtocolLine $protocolLine): Rank
    {
        $lastRank = new Rank();
        $lastRank->person_id = $protocolLine->person_id;
        $lastRank->event_id = $protocolLine->event->id;
        $lastRank->rank = $protocolLine->complete_rank;
        $lastRank->start_date = $protocolLine->event->date;
        $lastRank->finish_date = $protocolLine->event->date->clone()->addYears(2);
        return $lastRank;
    }
}
