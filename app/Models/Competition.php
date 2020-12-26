<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

/**
 * Class Competition
 *
 * @package App\Models
 * @property int $id
 * @property int $name
 * @property string $description
 * @property Carbon $from
 * @property Carbon $to
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection|Event[] $events
 * @property-read int|null $events_count
 * @method static Builder|Competition find(mixed $ids)
 * @method static Builder|Competition newModelQuery()
 * @method static Builder|Competition newQuery()
 * @method static Builder|Competition query()
 * @method static Builder|Competition whereCreatedAt($value)
 * @method static Builder|Competition whereDescription($value)
 * @method static Builder|Competition whereFrom($value)
 * @method static Builder|Competition whereId($value)
 * @method static Builder|Competition whereName($value)
 * @method static Builder|Competition whereTo($value)
 * @method static Builder|Competition whereUpdatedAt($value)
 */
class Competition extends Model
{
    protected $table = 'competitions';

    protected $fillable = [
        'name', 'description', 'from', 'to'
    ];

    protected $dates = ['from', 'to'];

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }
}
