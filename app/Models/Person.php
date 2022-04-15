<?php

namespace App\Models;

use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $lastname
 * @property string $firstname
 * @property Carbon|null $birthday
 * @property int $club_id
 * @property bool $from_base
 *
 * @property-read ProtocolLine[]|Collection $protocolLines
 * @property-read Club $club
 * @property-read PersonPrompt[]|Collection $prompts
 * @property-read PersonPayment[]|Collection $payments
 * @property-read Rank[]|Collection $ranks
 *
 * @method static Person|Builder find(mixed $ids)
 * @method static Person|Builder with(mixed $ids)
 * @method static Person|Builder whereIn(string $column, array|Collection $value)
 * @method static Collection get()
 * @method static Person|Builder whereClubId(int $clubId)
 * @method static Person|Builder whereFirstname(string $firstname)
 * @method static Person|Builder whereLastname(string $lastname)
 * @method static Person|Builder whereBirthday(Carbon $date)
 * @method static \Illuminate\Database\Query\Builder|Person selectRaw(Expression $raw)
 * @method static Paginator paginate()
 */
class Person extends Model
{
    public $timestamps = false;
    protected $table = 'person';
    protected $dates = ['birthday'];
    protected $casts = ['prompt' => 'array'];
    protected $fillable = ['lastname', 'firstname', 'birthday', 'club_id'];

    public function protocolLines(): HasMany
    {
        return $this->hasMany(ProtocolLine::class);
    }

    public function prompts(): HasMany
    {
        return $this->hasMany(PersonPrompt::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PersonPayment::class);
    }

    public function ranks(): HasMany
    {
        return $this->hasMany(Rank::class);
    }

    public function club(): HasOne
    {
        return $this->hasOne(Club::class, 'id', 'club_id');
    }
}
