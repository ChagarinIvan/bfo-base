<?php

declare(strict_types=1);

namespace App\Domain\Distance;

use App\Domain\Event\Event;
use App\Domain\Group\Group;
use App\Domain\ProtocolLine\ProtocolLine;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property int $group_id
 * @property int $event_id
 * @property int $length
 * @property int $points
 * @property false $disqual
 *
 * @property-read Group $group
 * @property-read Event $event
 * @property-read ProtocolLine[]|Collection $protocolLines
 */
class Distance extends Model
{
    use HasFactory;

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

    public function protocolLines(): HasMany|Builder
    {
        return $this->hasMany(ProtocolLine::class, 'distance_id', 'id');
    }
}
