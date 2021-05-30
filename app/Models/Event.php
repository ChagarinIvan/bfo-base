<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

/**
 * Class Event
 *
 * @package App\Models
 * @property int $id
 * @property string $name
 * @property string $description
 * @property Carbon $date
 * @property int $competition_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Competition|null $competition
 * @property-read Collection|ProtocolLine[] $protocolLines
 * @property-read Collection|CupEvent[] $cups
 * @property-read Collection|Flag[] $flags
 * @method static Builder|Event find(mixed $ids)
 * @method static Builder|Event newModelQuery()
 * @method static Builder|Event newQuery()
 * @method static Builder|Event query()
 * @method static Builder|Event with(mixed $params)
 * @method static Builder|Event where(mixed ... $args)
 * @method static Builder|Event whereCompetitionId($value)
 * @method static Builder|Event whereCreatedAt($value)
 * @method static Builder|Event whereDate($value)
 * @method static Builder|Event whereDescription($value)
 * @method static Builder|Event whereId($value)
 * @method static Builder|Event whereName($value)
 * @method static Builder|Event whereType($value)
 * @method static Builder|Event whereUpdatedAt($value)
 */
class Event extends Model
{
    protected $table = 'events';

    protected $fillable = [
        'name', 'description', 'date'
    ];

    protected $dates = ['date'];

    public function competition(): HasOne
    {
        return $this->hasOne(Competition::class, 'id', 'competition_id');
    }

    public function protocolLines(): HasMany
    {
        return $this->hasMany(ProtocolLine::class);
    }

    public function cups(): HasMany
    {
        return $this->hasMany(CupEvent::class);
    }

    public function flags(): BelongsToMany
    {
        return $this->belongsToMany(Flag::class, 'event_flags');
    }
}
