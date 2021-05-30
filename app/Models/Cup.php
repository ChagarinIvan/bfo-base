<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

/**
 * Class Cup
 *
 * @package App\Models
 * @property int $id
 * @property string $name
 * @property int $year
 * @property-read Collection|CupEvent[] $events
 * @property-read Collection|Group[] $groups
 * @method static Builder|Cup find(mixed $ids)
 * @method static Builder|Cup with(mixed $ids)
 */
class Cup extends Model
{
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(Group::class, 'cup_groups');
    }

    public function events(): HasMany
    {
        return $this->hasMany(CupEvent::class);
    }
}
