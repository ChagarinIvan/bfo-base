<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

/**
 * Class IdentLine
 *
 * @package App\Models
 * @property int $id
 * @property string $ident_line
 * @method static Builder|IdentLine whereIdentLine(string $preparedLine)
 * @method static Builder|IdentLine whereIn(string $column, array $list)
 * @method static IdentLine first()
 */
class IdentLine extends Model
{
    public $timestamps = false;
    protected $table = 'protocol_ident_queue';
}
