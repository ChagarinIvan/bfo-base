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
 *
 * @method static Group|null find(int $id)
 * @method static Builder|Group whereName(string $value)
 * @method static Builder|Group where(string $column, string $operator, string $value)
 * @method static Collection get()
 * @method static Collection all()
 * @method static Group|null first()
 * @method static Builder|Group with(mixed $params)
 * @method static Builder|Group selectRaw(Expression $raw)
 * @method static Builder|Group join(string $table, string $tableId, string $operator, string $joinId)
 */
class Group extends Model
{
    public $timestamps = false;
    protected $table = 'groups';

    public function distances(): HasMany
    {
        return $this->hasMany(Distance::class, 'group_id', 'id');
    }
}
