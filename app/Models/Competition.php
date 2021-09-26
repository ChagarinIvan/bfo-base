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
 * @method static Builder|Competition|null find(mixed $ids)
 * @method static Builder|Competition where(string $column, string $operator, string|int $value)
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
