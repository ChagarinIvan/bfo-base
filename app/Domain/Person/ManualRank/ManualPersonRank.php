<?php

declare(strict_types=1);

namespace App\Domain\Person\ManualRank;

use App\Domain\Auth\Impression;
use App\Domain\Person\ManualRank\Event\ManualPersonRankAdded;
use App\Domain\Person\ManualRank\Event\ManualPersonRankDisabled;
use App\Domain\Person\RankFact;

final class ManualPersonRank
{
    /** @var list<object> */
    private array $events = [];

    public function __construct(
        public readonly string $id,
        public readonly int $personId,
        public readonly ManualRankInput $input,
        public readonly Impression $created,
        private Impression $updated,
        private bool $active = true,
    ) {
    }

    public function create(): void
    {
        $this->events[] = new ManualPersonRankAdded($this->id, $this->personId);
    }

    public function disable(Impression $impression): void
    {
        if (!$this->active) {
            return;
        }
        $this->active = false;
        $this->updated = $impression;
        $this->events[] = new ManualPersonRankDisabled($this->id, $this->personId);
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function updated(): Impression
    {
        return $this->updated;
    }

    /** @return list<object> */
    public function drainEvents(): array
    {
        $events = $this->events;
        $this->events = [];
        return $events;
    }

    public function fact(): RankFact
    {
        return new RankFact(
            null,
            null,
            null,
            null,
            $this->input->rank,
            $this->input->achievedOn->copy(),
            $this->input->activatedOn->copy(),
            manualRankId: $this->id
        );
    }
}
