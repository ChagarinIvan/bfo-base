<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

/**
 * Class EventFlag
 *
 * @package App\Models
 * @property int $id
 * @property string $name
 * @property string $color
 * @property-read Collection|Event[] $events
 */
class EventFlag extends Model
{
    protected $table = 'event_flag';

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }
}
