<?php

namespace App\Models;

use App\Services\IdentService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

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
 * @property PersonPrompt[]|Collection $prompts
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

    public function protocolLines(): HasMany
    {
        return $this->hasMany(ProtocolLine::class);
    }

    public function prompts(): HasMany
    {
        return $this->hasMany(PersonPrompt::class);
    }

    public function club(): HasOne
    {
        return $this->hasOne(Club::class, 'id', 'club_id');
    }

    public function makePrompts(): void
    {
        $personData = [
            IdentService::prepareLine(mb_strtolower($this->lastname)),
            IdentService::prepareLine(mb_strtolower($this->firstname)),
        ];
        $personLine = implode('_', $personData);
        $prompt = new PersonPrompt();
        $prompt->person_id = $this->id;
        $prompt->prompt = $personLine;
        $prompt->save();

        if ($this->birthday !== null) {
            $personData[] = $this->birthday->format('Y');
            $personLine = implode('_', $personData);
            $prompt = new PersonPrompt();
            $prompt->person_id = $this->id;
            $prompt->prompt = $personLine;
            $prompt->save();
        }
    }
}
