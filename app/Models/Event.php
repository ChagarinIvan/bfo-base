<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Event
 *
 * @package App\Models
 * @property int $id
 * @property int $name
 * @property string $type
 * @property string $description
 * @property Carbon $date
 * @property int $competition_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Competition|null $competition
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ProtocolLine[] $protocolLines
 * @property-read int|null $protocol_lines_count
 * @method static \Illuminate\Database\Eloquent\Builder|Event newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Event newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Event query()
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereCompetitionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Event extends Model
{
    protected $table = 'events';

    protected $fillable = [
        'name', 'description', 'type', 'date'
    ];

    protected $dates = ['date'];

    public function competition()
    {
        return $this->hasOne(Competition::class);
    }

    public function protocolLines()
    {
        return $this->hasMany(ProtocolLine::class);
    }
}
