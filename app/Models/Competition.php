<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Competition
 * @package App\Models
 * @property int $id
 * @property int $name
 * @property string $description
 * @property Carbon $from
 * @property Carbon $to
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
