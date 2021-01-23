<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
 * @method static Builder|Flag find(mixed $ids)
 */
class Flag extends Model
{
    protected $table = 'flags';

    protected $fillable = [
        'name', 'color'
    ];

    public $timestamps = false;

    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_flags');
    }
}
