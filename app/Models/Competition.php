<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Competition
 *
 * @package App\Models
 * @property int $id
 * @property int $name
 * @property string $description
 * @property Carbon $from
 * @property Carbon $to
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Event[] $events
 * @property-read int|null $events_count
 * @method static \Illuminate\Database\Eloquent\Builder|Competition newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Competition newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Competition query()
 * @method static \Illuminate\Database\Eloquent\Builder|Competition whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Competition whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Competition whereFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Competition whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Competition whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Competition whereTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Competition whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Competition extends Model
{
    protected $table = 'competitions';

    protected $fillable = [
        'name', 'description', 'from', 'to'
    ];

    protected $dates = ['from', 'to'];

    public function events()
    {
        return $this->hasMany(Event::class);
    }
}
