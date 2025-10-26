<?php

declare(strict_types=1);

namespace App\Application\Handler\Rank;

use App\Domain\Person\Event\PersonDisabled;
use App\Domain\Rank\Rank;
use App\Domain\Rank\RankRepository;
use App\Domain\Shared\Criteria;
use Illuminate\Contracts\Queue\ShouldQueue;

final readonly class RemoveRanksOnDisablePersonHandler implements ShouldQueue
{
    public function __construct(private RankRepository $ranks)
    {
    }

    public function handle(PersonDisabled $event): void
    {
        /** @var Rank $rank */
        foreach ($this->ranks->byCriteria(new Criteria(['person_id' => $event->person->id])) as $rank) {
            $rank->delete();
        }
    }
}
