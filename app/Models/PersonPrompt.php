<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class PersonPrompt
 *
 * @package App\Models
 * @property int $id
 * @property int $person_id
 * @property string $prompt
 * @property Person $person
 * @method static Builder|PersonPrompt wherePrompt(string $line)
 * @method static Builder|PersonPrompt where(...$value)
 * @method static Builder|PersonPrompt selectRaw(string $row)
 * @method static PersonPrompt[]|Collection get()
 */
class PersonPrompt extends Model
{
    public $timestamps = false;
    protected $table = 'persons_prompt';

    public static function getPersonsByLevenshtein(string $preparedLine, int $value): Collection
    {
        $lev = DB::raw("levenshtein(`prompt`, '{$preparedLine}')");
        return self::where($lev, '<=',  $value)
            ->orderBy($lev)
            ->get();
    }

    public function person(): HasOne
    {
        return $this->hasOne(Person::class);
    }
}
