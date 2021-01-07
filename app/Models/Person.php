<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Class Person
 *
 * @package App\Models
 * @property int $id
 * @property string $lastname
 * @property string $firstname
 * @property string|null $patronymic
 * @property Carbon|null $birthday
 * @property ProtocolLine[]|Collection $protocolLines
 * @method static Builder|Person find(mixed $ids)
 */
class Person extends Model
{
    public $timestamps = false;
    protected $table = 'person';
    protected $dates = ['birthday'];

    public function protocolLines(): HasMany
    {
        return $this->hasMany(ProtocolLine::class);
    }
}
