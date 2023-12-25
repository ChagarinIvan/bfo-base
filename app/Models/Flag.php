<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $name
 * @property string $color
 *
 * @property-read Collection|Event[] $events
 *
 * @method static Builder|Flag find(mixed $ids)
 * @method static Builder|Flag with(mixed $params)
 */
class Flag extends Model
{
    public $timestamps = false;
    protected $table = 'flags';

    protected $fillable = [
        'name', 'color'
    ];

    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_flags');
    }
}
