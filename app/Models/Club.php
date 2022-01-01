<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;

/**
 * Class Club
 *
 * @package App\Models
 * @property int $id
 * @property string $name
 * @property string $normalize_name
 * @property Person[]|Collection $persons
 * @method static Builder|Club find(mixed $ids)
 * @method static Builder|Club with(mixed $with)
 * @method static Builder|Club whereNormalizeName(string $name)
 * @method static Builder|Club orderBy(string $column)
 * @method static Club[]|Paginator paginate(int $size)
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
