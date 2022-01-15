<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $name
 * @property string $normalize_name
 *
 * @property-read  Person[]|Collection $persons
 *
 * @method static Builder|Club whereNormalizeName(string $name)
 * @method static Collection get()
 * @method static Club|null first()
 * @method static Builder|Club orderBy(string $column)
 */
class Club extends Model
{
    public $timestamps = false;
    protected $table = 'club';

    public function persons(): HasMany
    {
        return $this->hasMany(Person::class);
    }
}
