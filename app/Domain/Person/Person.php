<?php

declare(strict_types=1);

namespace App\Domain\Person;

use App\Domain\Auth\Impression;
use App\Domain\Club\Club;
use App\Domain\Person\Event\PersonCreated;
use App\Domain\Person\Event\PersonInfoUpdated;
use App\Domain\PersonPayment\PersonPayment;
use App\Domain\PersonPrompt\PersonPrompt;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Infrastracture\Laravel\Eloquent\Auth\ImpressionCast;
use App\Models\Rank;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;

/**
 * @property int $id
 * @property string $lastname
 * @property string $firstname
 * @property Carbon|null $birthday
 * @property int|null $club_id
 * @property bool $from_base
 *
 * @property Impression $created
 * @property Impression $updated
 *
 * @property-read int $protocol_lines_count
 *
 * @property-read ProtocolLine[]|Collection $protocolLines
 * @property-read null|Club $club
 * @property-read PersonPrompt[]|Collection $prompts
 * @property-read PersonPayment[]|Collection $payments
 * @property-read Rank[]|Collection $ranks
 */
class Person extends Model
{
    use HasFactory;

    protected $table = 'person';

    protected $casts = [
        'prompt' => 'array',
        'birthday' => 'datetime:Y-m-d',
        'created' => ImpressionCast::class,
        'updated' => ImpressionCast::class,
    ];

    protected $fillable = ['lastname', 'firstname', 'birthday', 'club_id', 'from_base', 'created', 'updated'];

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

    public function updateInfo(PersonInfo $info, Impression $impression): void
    {
        $this->lastname = $info->lastname;
        $this->firstname = $info->firstname;
        $this->club_id = $info->clubId;
        $this->birthday = $info->birthday;

        $this->updated = $impression;

        if (App::environment() !== 'testing') {
            event(new PersonInfoUpdated($this));
        }
    }

    public function create(): void
    {
        $this->save();

        event(new PersonCreated($this));
    }
}
