<?php
declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $lastname
 * @property string $firstname
 * @property Carbon|null $birthday
 * @property int|null $club_id
 * @property bool $from_base
 * @property int $protocol_lines_count
 *
 * @property-read ProtocolLine[]|Collection $protocolLines
 * @property-read null|Club $club
 * @property-read PersonPrompt[]|Collection $prompts
 * @property-read PersonPayment[]|Collection $payments
 * @property-read Rank[]|Collection $ranks
 *
 * @method static Person|Builder|null find(mixed $ids)
 * @method static Person|Builder with(mixed $ids)
 * @method static Person|Builder withCount(string $ids)
 * @method static Person|Builder whereIn(string $column, array|Collection $value)
 * @method Person|Builder where(string $column, string $operator, string $value)
 * @method Person|Builder orWhere(string|Expression $column, string $operator, string $value)
 * @method Person|Builder orWhereHas(string $column, callable $callable)
 * @method static Collection get()
 * @method static Person|Builder whereClubId(int $clubId)
 * @method static Person|Builder whereFirstname(string $firstname)
 * @method static Person|Builder whereLastname(string $lastname)
 * @method static Person|Builder whereBirthday(Carbon $date)
 * @method static Person|Builder selectRaw(Expression $raw)
 * @method Person|Builder orderBy(string $column, string $sortMode)
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
