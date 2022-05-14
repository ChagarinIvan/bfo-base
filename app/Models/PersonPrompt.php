<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property int $person_id
 * @property string $prompt
 * @property string $metaphone
 *
 * @property-read Person $person
 *
 * @method static null|PersonPrompt find(int $id)
 * @method static Builder|PersonPrompt wherePrompt(string $line)
 * @method static Builder|PersonPrompt where(...$value)
 * @method static Builder|PersonPrompt selectRaw(string $row)
 * @method static PersonPrompt[]|Collection get()
 */
class PersonPrompt extends Model
{
    public $timestamps = false;
    protected $table = 'persons_prompt';
    protected $fillable = ['prompt'];

    public function person(): HasOne
    {
        return $this->hasOne(Person::class);
    }
}
