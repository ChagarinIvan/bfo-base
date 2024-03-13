<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $name
 *
 * @property-read Distance[]|Collection $distances
 */
class Group extends Model
{
    public $timestamps = false;
    protected $table = 'groups';

    public function distances(): HasMany|Builder
    {
        return $this->hasMany(Distance::class, 'group_id', 'id');
    }
}
