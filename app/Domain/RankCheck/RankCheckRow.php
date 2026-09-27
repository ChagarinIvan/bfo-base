<?php

declare(strict_types=1);

namespace App\Domain\RankCheck;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $position
 * @property int $rank_check_id
 * @property string|null $group
 * @property string $name
 * @property string|null $club
 * @property string|null $rank
 * @property string|null $number
 * @property string|null $year
 * @property int|null $person_id
 * @property string|null $database_name
 * @property string|null $database_club
 * @property string|null $database_rank
 * @property string|null $database_year
 * @property bool $has_person
 * @property bool $is_equal
 */

#[Fillable([
    'rank_check_id', 'position', 'group', 'name', 'club', 'rank', 'number', 'year',
    'person_id', 'database_name', 'database_club', 'database_rank', 'database_year',
    'has_person', 'is_equal',
])]
#[Table(name: 'rank_check_rows')]
class RankCheckRow extends Model
{
    protected function casts(): array
    {
        return [
            'number' => 'string',
            'year' => 'string',
            'database_year' => 'string',
            'has_person' => 'boolean',
            'is_equal' => 'boolean',
        ];
    }
}
