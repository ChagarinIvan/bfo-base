<?php

declare(strict_types=1);

namespace App\Infrastructure\Laravel\Eloquent\RankCheck;

use App\Domain\RankCheck\RankCheckRow;
use App\Domain\RankCheck\RankCheckRowRepository;
use App\Domain\Shared\Criteria;
use App\Domain\Shared\Pagination\Slice;
use App\Infrastructure\Laravel\Eloquent\Pagination\EloquentQueryAdapter;
use App\Infrastructure\Laravel\Eloquent\Shared\EscapesLikePatterns;
use Illuminate\Database\Eloquent\Builder;
use function mb_strtolower;

final readonly class EloquentRankCheckRowRepository implements RankCheckRowRepository
{
    use EscapesLikePatterns;

    public function add(RankCheckRow $row): void
    {
        $row->save();
    }

    /** @return Slice<RankCheckRow> */
    public function paginate(Criteria $criteria): Slice
    {
        $query = RankCheckRow::query()
            ->orderBy('position')
            ->orderBy('rank_check_rows.id');

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

        $this->applyTextFilter($query, $criteria, 'name');
        $this->applyTextFilter($query, $criteria, 'group');

        if ($criteria->hasParam('hasPerson')) {
            $query->where('has_person', $criteria->param('hasPerson'));
        }
        if ($criteria->hasParam('isEqual')) {
            $query->where('is_equal', $criteria->param('isEqual'));
        }

        return new Slice(new EloquentQueryAdapter(
            $query,
        ));
    }

    private function applyTextFilter(Builder $query, Criteria $criteria, string $field): void
    {
        if (!$criteria->hasParam($field)) {
            return;
        }

        $value = $this->escapeLikePattern(
            mb_strtolower((string) $criteria->param($field)),
        );

        $query->whereRaw("LOWER(`{$field}`) LIKE ? ESCAPE '!'", ['%' . $value . '%']);
    }
}
