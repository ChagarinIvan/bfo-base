<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Event
 * @package App\Models
 * @property int $id
 * @property int $name
 * @property string $type
 * @property string $description
 * @property Carbon $date
 * @property int $competition_id
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
