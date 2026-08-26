<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

/**
 * @property int $id
 * @property string $ident_line
 *
 * @method static Builder|IdentLine whereIdentLine(string $preparedLine)
 * @method static IdentLine|null first()
 */
#[Table(name: 'protocol_ident_queue')]
#[WithoutTimestamps]
class IdentLine extends Model
{
}
