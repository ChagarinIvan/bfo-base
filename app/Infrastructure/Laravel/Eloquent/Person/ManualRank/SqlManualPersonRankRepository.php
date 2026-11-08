<?php

declare(strict_types=1);

namespace App\Infrastructure\Laravel\Eloquent\Person\ManualRank;

use App\Domain\Auth\Impression;
use App\Domain\Person\ManualRank\ManualPersonRank;
use App\Domain\Person\ManualRank\ManualPersonRankRepository;
use App\Domain\Person\ManualRank\ManualRankInput;
use App\Domain\Rank\Rank;
use App\Domain\Shared\Criteria;
use Carbon\Carbon;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use stdClass;

final readonly class SqlManualPersonRankRepository implements ManualPersonRankRepository
{
    public function __construct(private Connection $db, private Dispatcher $events)
    {
    }

    public function byCriteria(Criteria $criteria): array
    {
        return $this->query($criteria)->orderBy('achieved_on')->orderBy('id')->get()->map($this->hydrate(...))->all();
    }

    public function oneByCriteria(Criteria $criteria): ?ManualPersonRank
    {
        $row = $this->query($criteria)->first();
        return $row === null ? null : $this->hydrate($row);
    }

    public function add(ManualPersonRank $rank): void
    {
        $rank->create();
        $this->db->table('manual_person_ranks')->insert([
            'id' => $rank->id, 'person_id' => $rank->personId, 'rank' => $rank->input->rank->value,
            'achieved_on' => $rank->input->achievedOn->format('Y-m-d'),
            'activated_on' => $rank->input->activatedOn->format('Y-m-d'),
            'active' => $rank->isActive(), 'created_at' => $rank->created->at,
            'created_by' => $rank->created->by, 'updated_at' => $rank->updated()->at,
            'updated_by' => $rank->updated()->by,
        ]);
        $this->publishAfterCommit($rank);
    }

    public function update(ManualPersonRank $rank): void
    {
        $this->db->table('manual_person_ranks')->where('id', $rank->id)->update([
            'active' => $rank->isActive(), 'updated_at' => $rank->updated()->at, 'updated_by' => $rank->updated()->by,
        ]);
        $this->publishAfterCommit($rank);
    }

    private function query(Criteria $criteria): Builder
    {
        $query = $this->db->table('manual_person_ranks')->where('active', true);
        foreach (['id' => 'id', 'personId' => 'person_id', 'rank' => 'rank', 'achievedOn' => 'achieved_on', 'activatedOn' => 'activated_on'] as $key => $column) {
            if ($criteria->hasParam($key)) {
                $query->where($column, $criteria->param($key));
            }
        }
        return $query;
    }

    private function hydrate(stdClass $row): ManualPersonRank
    {
        return new ManualPersonRank(
            $row->id,
            (int) $row->person_id,
            new ManualRankInput(Rank::from((int) $row->rank), Carbon::parse($row->achieved_on), Carbon::parse($row->activated_on)),
            new Impression(Carbon::parse($row->created_at), (int) $row->created_by),
            new Impression(Carbon::parse($row->updated_at), (int) $row->updated_by),
            (bool) $row->active
        );
    }

    private function publishAfterCommit(ManualPersonRank $rank): void
    {
        foreach ($rank->drainEvents() as $event) {
            $this->db->afterCommit(fn () => $this->events->dispatch($event));
        }
    }
}
