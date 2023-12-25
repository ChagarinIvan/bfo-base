<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

/**
 * @property int $id
 * @property string $ident_line
 *
 * @method static Builder|IdentLine whereIdentLine(string $preparedLine)
 * @method static IdentLine|null first()
 */
class IdentLine extends Model
{
    public $timestamps = false;
    protected $table = 'protocol_ident_queue';
}
