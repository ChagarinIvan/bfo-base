<?php

declare(strict_types=1);

namespace App\Infrastructure\Laravel\Eloquent\Person\RankAudit;

use App\Domain\Auth\Impression;
use App\Domain\Person\Audit\RankAuditAction;
use App\Domain\Person\Audit\RankAuditTrigger;
use App\Domain\Person\PersonRank;
use App\Domain\Rank\Rank;
use App\Infrastructure\Laravel\Eloquent\Auth\ImpressionCast;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $person_id
 * @property RankAuditTrigger $trigger
 * @property Impression $created
 * @property array{rankId:int, startedOn:?string, activatedOn:?string, finishedOn:?string, nextActivationOn:?string} $applied
 */
#[Table(name: 'person_rank_audit')]
#[WithoutTimestamps]
final class RankAuditModel extends Model
{
    public static function fromAction(RankAuditAction $action): self
    {
        $model = new self();
        $model->person_id = $action->personId;
        $model->trigger = $action->trigger;
        $model->created = $action->created;
        $model->applied = [
            'rankId' => $action->current->rank->value,
            'startedOn' => $action->current->startedOn?->toDateString(),
            'activatedOn' => $action->current->activatedOn?->toDateString(),
            'finishedOn' => $action->current->finishedOn?->toDateString(),
            'nextActivationOn' => $action->nextActivationOn?->toDateString(),
        ];

        return $model;
    }

    public function toAction(): RankAuditAction
    {
        $applied = $this->applied;

        return new RankAuditAction(
            $this->person_id,
            $this->trigger,
            $this->created,
            new PersonRank(
                Rank::from($applied['rankId']),
                $applied['startedOn'] === null ? null : Carbon::parse($applied['startedOn']),
                $applied['activatedOn'] === null ? null : Carbon::parse($applied['activatedOn']),
                $applied['finishedOn'] === null ? null : Carbon::parse($applied['finishedOn']),
            ),
            $applied['nextActivationOn'] === null ? null : Carbon::parse($applied['nextActivationOn']),
            $this->id,
        );
    }

    protected function casts(): array
    {
        return [
            'trigger' => RankAuditTrigger::class,
            'created' => ImpressionCast::class,
            'applied' => 'array',
        ];
    }
}
