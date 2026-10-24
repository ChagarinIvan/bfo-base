<?php

declare(strict_types=1);

namespace App\Infrastructure\Laravel\Eloquent\Person\RankAudit;

use App\Domain\Person\Audit\RankAuditAction;
use App\Domain\Person\Audit\RankAuditRepository;
use App\Domain\Shared\Criteria;
use App\Domain\Shared\Pagination\Slice;
use App\Infrastructure\Laravel\Eloquent\Pagination\EloquentQueryAdapter;

final readonly class EloquentRankAuditRepository implements RankAuditRepository
{
    public function byCriteria(Criteria $criteria): Slice
    {
        $query = RankAuditModel::query()
            ->join('person', 'person.id', '=', 'person_rank_audit.person_id')
            ->where('person.active', true)
            ->where('person_rank_audit.person_id', $criteria->param('personId'))
            ->select('person_rank_audit.*')
            ->orderByDesc('person_rank_audit.id');

        return new Slice(new EloquentQueryAdapter($query))
            ->map(static fn (RankAuditModel $model): RankAuditAction => $model->toAction());
    }
}
