<?php

declare(strict_types=1);

namespace App\Domain\Person;

use App\Domain\Auth\Impression;
use App\Domain\Club\Club;
use App\Domain\Person\Event\PersonCreated;
use App\Domain\Person\Event\PersonDisabled;
use App\Domain\Person\Event\PersonInfoUpdated;
use App\Domain\PersonPayment\PersonPayment;
use App\Domain\PersonPrompt\PersonPrompt;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\Shared\AggregatedModel;
use App\Infrastracture\Laravel\Eloquent\Auth\ImpressionCast;
use App\Models\Rank;
use Carbon\Carbon;
use Database\Factories\Domain\Person\PersonFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Collection;

/**
 * @property int $id
 * @property string $lastname
 * @property string $firstname
 * @property Carbon|null $birthday
 * @property int|null $club_id
 * @property Citizenship $citizenship
 * @property bool $from_base
 * @property bool $active
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
 *
 * @see PersonFactory
 */
class Person extends AggregatedModel
{
    use HasFactory;

    protected $table = 'person';

    protected $casts = [
        'prompt' => 'array',
        'citizenship' => Citizenship::class,
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
        $this->citizenship = $info->citizenship;

        $this->updated = $impression;

        $this->recordThat(new PersonInfoUpdated($this));
    }

    public function create(): void
    {
        $this->recordThat(new PersonCreated($this));

        $this->save();
    }

    public function disable(Impression $impression): void
    {
        $this->updated = $impression;
        $this->active = false;

        $this->recordThat(new PersonDisabled($this));
    }
}
