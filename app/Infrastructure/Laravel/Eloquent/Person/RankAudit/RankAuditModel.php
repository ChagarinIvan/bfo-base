<?php

declare(strict_types=1);

namespace App\Infrastructure\Laravel\Eloquent\Person\RankAudit;

use App\Domain\Auth\Impression;
use App\Domain\Person\Audit\RankAuditTrigger;
use App\Infrastructure\Laravel\Eloquent\Auth\ImpressionCast;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Model;

/** @property int $id
 * @property int $person_id
 * @property RankAuditTrigger $trigger
 * @property Impression $created
 * @property array<string, mixed>|null $before_state
 * @property array<string, mixed>|null $after_state
 */
#[Table(name: 'person_rank_audit')]
#[WithoutTimestamps]
final class RankAuditModel extends Model
{
    protected function casts(): array
    {
        return ['trigger' => RankAuditTrigger::class, 'created' => ImpressionCast::class,
            'before_state' => 'array', 'after_state' => 'array'];
    }
}
