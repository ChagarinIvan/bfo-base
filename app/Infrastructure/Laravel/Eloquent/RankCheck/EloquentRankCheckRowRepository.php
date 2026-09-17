<?php

declare(strict_types=1);

namespace App\Infrastructure\Laravel\Eloquent\RankCheck;

use App\Domain\RankCheck\RankCheckRow;
use App\Domain\RankCheck\RankCheckRowRepository;
use App\Domain\Shared\Criteria;
use App\Domain\Shared\Pagination\Slice;
use App\Infrastructure\Laravel\Eloquent\Pagination\EloquentQueryAdapter;

final readonly class EloquentRankCheckRowRepository implements RankCheckRowRepository
{
    public function add(RankCheckRow $row): void
    {
        $row->save();
    }

    /** @return Slice<RankCheckRow> */
    public function paginateRows(Criteria $criteria): Slice
    {
        $query = RankCheckRow::query()->orderBy('position');
        if ($criteria->hasParam('rankCheckId')) {
            $query->where('rank_check_id', $criteria->param('rankCheckId'));
        }
        if ($criteria->hasParam('status')) {
            $query
                ->join('rank_checks', 'rank_checks.id', '=', 'rank_check_rows.rank_check_id')
                ->where('rank_checks.status', $criteria->param('status'))
                ->select('rank_check_rows.*')
            ;
        }

        return new Slice(new EloquentQueryAdapter(
            $query,
        ));
    }
}
