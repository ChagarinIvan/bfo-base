<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

/**
 * Class SystemFlag
 *
 * @package App\Models
 * @property int $id
 * @property string $key
 * @property string $volume
 * @method static Builder|SystemFlag find(mixed $ids)
 * @method static Builder|SystemFlag where(string $index, string $value)
 */
class SystemFlag extends Model
{
    public $timestamps = false;
    protected $table = 'system_flags';
}
