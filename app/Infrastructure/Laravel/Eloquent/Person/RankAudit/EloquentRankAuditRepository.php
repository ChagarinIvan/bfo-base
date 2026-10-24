<?php

declare(strict_types=1);

namespace App\Infrastructure\Laravel\Eloquent\Person\RankAudit;

use App\Domain\Person\Audit\RankAuditEntry;
use App\Domain\Person\Audit\RankAuditRepository;
use App\Domain\Person\Audit\RankSnapshot;
use App\Domain\Person\Person;
use App\Domain\Person\PersonRankHistory;
use App\Domain\Shared\Criteria;
use App\Domain\Shared\Pagination\Slice;
use App\Infrastructure\Laravel\Eloquent\Pagination\EloquentQueryAdapter;

final readonly class EloquentRankAuditRepository implements RankAuditRepository
{
    public function __construct(private RankSnapshotMapper $mapper)
    {
    }

    public function snapshot(int $personId): RankSnapshot
    {
        $person = Person::query()->findOrFail($personId);

        return RankSnapshot::fromValues(
            $person->currentRank(),
            PersonRankHistory::query()->where('person_id', $personId)->get()->all(),
            $person->next_rank_activation_on
        );
    }

    public function add(RankAuditEntry $entry): void
    {
        $model = new RankAuditModel();
        $model->person_id = $entry->personId;
        $model->trigger = $entry->trigger;
        $model->created = $entry->created;
        $model->before_state = $this->mapper->toStorage($entry->before);
        $model->after_state = $this->mapper->toStorage($entry->after);
        $model->save();
    }

    public function byCriteria(Criteria $criteria): Slice
    {
        return new Slice(new EloquentQueryAdapter(RankAuditModel::query()
            ->where('person_id', $criteria->param('personId'))->orderByDesc('id')))
            ->map(fn (RankAuditModel $model): RankAuditEntry => new RankAuditEntry(
                $model->person_id,
                $model->trigger,
                $model->created,
                $this->mapper->fromStorage($model->before_state),
                $this->mapper->fromStorage($model->after_state),
                $model->id,
            ));
    }
}
