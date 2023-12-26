<?php

declare(strict_types=1);

namespace App\Domain\Rank;

use App\Domain\Auth\Impression;
use App\Domain\Rank\Event\RankAdded;
use App\Domain\Rank\Factory\FinishDateCalculator;
use Carbon\Carbon;

final class Rank
{
    private ?Carbon $startedAt;
    private ?Carbon $finishedAt;
    private Impression $updated;
    private readonly Impression $created;

    public function __construct(
        private readonly RankId $id,
        private readonly int $personId,
        private readonly ?int $eventId,
        private readonly RankType $type,
        private readonly Carbon $completedAt,
        Impression $impression,
    ) {
        $this->updated = $impression;
        $this->created = $impression;

         $event = new RankAdded(
             id: $id,
             personId: $this->personId,
             eventId: $this->eventId,
             type: $this->type,
             completedAt: $this->completedAt,
             created: $this->created,
         );
    }

    public function activate(FinishDateCalculator $calculator, Carbon $startDate, Impression $impression): void
    {
        $this->startedAt = $startDate;
        $this->finishedAt = $calculator->calculate($startDate);

        $this->updated = $impression;
    }

    public function id(): RankId
    {
        return $this->id;
    }

    public function personId(): int
    {
        return $this->personId;
    }

    public function type(): RankType
    {
        return $this->type;
    }

    public function eventId(): int
    {
        return $this->eventId;
    }

    public function completedAt(): Carbon
    {
        return $this->completedAt;
    }

    public function startedAt(): ?Carbon
    {
        return $this->startedAt;
    }

    public function finishedA(): ?Carbon
    {
        return $this->finishedAt;
    }

    public function created(): Impression
    {
        return $this->created;
    }

    public function updated(): Impression
    {
        return $this->updated;
    }
}
