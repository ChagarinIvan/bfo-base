<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\Builder;
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
 * @property int $person_id
 * @property-read Event|null $event
 * @property-read Group|null $group
 * @property-read Person|null $person
 * @method static Builder|ProtocolLine find(mixed $ids)
 * @method static Builder|ProtocolLine newModelQuery()
 * @method static Builder|ProtocolLine newQuery()
 * @method static Builder|ProtocolLine query()
 * @method static Builder|ProtocolLine whereClub($value)
 * @method static Builder|ProtocolLine whereCompleteRank($value)
 * @method static Builder|ProtocolLine whereEventId($value)
 * @method static Builder|ProtocolLine whereFirstname($value)
 * @method static Builder|ProtocolLine whereGroupId($value)
 * @method static Builder|ProtocolLine wherePersonId($value)
 * @method static Builder|ProtocolLine whereId($value)
 * @method static Builder|ProtocolLine whereLastname($value)
 * @method static Builder|ProtocolLine wherePlace($value)
 * @method static Builder|ProtocolLine wherePoints($value)
 * @method static Builder|ProtocolLine whereRank($value)
 * @method static Builder|ProtocolLine whereRunnerNumber($value)
 * @method static Builder|ProtocolLine whereSerialNumber($value)
 * @method static Builder|ProtocolLine whereTime($value)
 * @method static Builder|ProtocolLine whereYear($value)
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

    public function group(): HasOne
    {
        return $this->hasOne(Group::class, 'id', 'group_id');
    }

    public function event(): HasOne
    {
        return $this->hasOne(Event::class, 'id', 'event_id');
    }

    public function person(): HasOne
    {
        return $this->hasOne(Person::class, 'id', 'person_id');
    }

    public function getIndentLine(): string
    {
        $data = [
            $this->lastname,
            $this->firstname,
        ];
        if ($this->year !== null) {
            $data[] = $this->year;
        }
        return mb_strtolower(implode('_', $data));
    }
}
