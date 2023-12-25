<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $name
 * @property string $description
 * @property Carbon $from
 * @property Carbon $to
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @property-read Collection|Event[] $events
 *
 * @method static Competition|null find(mixed $ids)
 * @method static Competition|Builder where(string $column, string $operator, string $value)
 */
class Competition extends Model
{
    protected $table = 'competitions';

    protected $dates = ['from', 'to'];

    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }
}
