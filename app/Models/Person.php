<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use JetBrains\PhpStorm\Pure;

/**
 * Class Person
 *
 * @package App\Models
 * @property int $id
 * @property string $lastname
 * @property string $firstname
 * @property Carbon|null $birthday
 * @property ProtocolLine[]|Collection $protocolLines
 * @property int $club_id
 * @property Club $club
 * @property string $prompt
 * @method static Person|Builder find(mixed $ids)
 * @method static Person|Builder with(mixed $ids)
 * @method static Person|Builder orderBy(string $column)
 * @method static Person|Builder whereClubId(int $clubId)
 * @method static Person[]|Paginator paginate(int $size)
 */
class Person extends Model
{
    public $timestamps = false;
    protected $table = 'person';
    protected $dates = ['birthday'];
    protected $casts = [
        'prompt' => 'array'
    ];
    protected $fillable = [
        'lastname', 'firstname', 'birthday', 'club_id'
    ];

    public function setPrompt(string $line): void
    {
        $this->prompt = array_unique(array_merge($this->getPrompts(), [$line]));
    }

    /**
     * @return string[]
     */
    #[Pure] public function getPrompts(): array
    {
        if (is_array($this->prompt)) {
            return $this->prompt;
        }

        return [];
    }

    public function protocolLines(): HasMany
    {
        return $this->hasMany(ProtocolLine::class);
    }

    public function club(): HasOne
    {
        return $this->hasOne(Club::class, 'id', 'club_id');
    }
}
