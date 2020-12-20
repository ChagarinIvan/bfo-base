<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Class ProtocolLine
 *
 * @package App\Models
 * @property int $id
 * @property int $serial_number
 * @property string $lastname
 * @property string $firstname
 * @property string $club
 * @property int $year
 * @property string $rank
 * @property int $runner_number
 * @property null|Carbon $time
 * @property null|int $place
 * @property string $complete_rank
 * @property null|int $points
 * @property int $event_id
 * @property int $group_id
 * @property-read \App\Models\Event|null $event
 * @property-read \App\Models\Group|null $group
 * @method static \Illuminate\Database\Eloquent\Builder|ProtocolLine newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProtocolLine newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProtocolLine query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProtocolLine whereClub($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProtocolLine whereCompleteRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProtocolLine whereEventId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProtocolLine whereFirstname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProtocolLine whereGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProtocolLine whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProtocolLine whereLastname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProtocolLine wherePlace($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProtocolLine wherePoints($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProtocolLine whereRank($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProtocolLine whereRunnerNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProtocolLine whereSerialNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProtocolLine whereTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProtocolLine whereYear($value)
 * @mixin \Eloquent
 */
class ProtocolLine extends Model
{
    public $timestamps = false;
    protected $table = 'protocol_lines';

    protected $dates = ['time'];
    protected $fillable = [
        'serial_number',
        'lastname',
        'firstname',
        'club',
        'year',
        'rank',
        'runner_number',
        'time',
        'place',
        'complete_rank',
        'points',
    ];
    public function group()
    {
        return $this->hasOne(Group::class, 'id', 'group_id');
    }

    public function event()
    {
        return $this->hasOne(Event::class, 'id', 'event_id');
    }
}
