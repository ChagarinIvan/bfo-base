<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property int $group_id
 * @property int $event_id
 * @property int $length
 * @property int $points
 *
 * @property-read Group $group
 * @property-read Event $event
 * @property-read ProtocolLine[]|Collection $protocolLines
 *
 * @method static Builder|Distance where(string $column, string $equal, string|int $value)
 * @method static Builder|Distance whereGroupId(int $groupId)
 * @method static Builder|Distance whereEventId(int $eventId)
 * @method static Builder|Distance whereLength(int $length)
 * @method static Builder|Distance wherePoints(int $points)
 * @method static Builder|Distance whereIn(string $column, array|Collection $eventGroups)
 * @method static Builder|Distance selectRaw(Expression $raw)
 * @method static Builder|Distance join(string $table, string $tableId, string $operator, string $joinId)
 * @method static Distance|null first()
 * @method static Collection get()
 */
class Distance extends Model
{
    public $timestamps = false;
    protected $table = 'distances';

    public function group(): BelongsTo
    {
        return $this->belongsTo(Group::class, 'group_id', 'id');
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class, 'event_id', 'id');
    }

    public function protocolLines(): HasMany
    {
        return $this->hasMany(ProtocolLine::class, 'distance_id', 'id');
    }
}
