<?php

declare(strict_types=1);

namespace App\Domain\Person;

use App\Domain\Auth\Impression;
use App\Domain\Club\Club;
use App\Domain\Person\Event\PersonCreated;
use App\Domain\Person\Event\PersonDisabled;
use App\Domain\Person\Event\PersonInfoUpdated;
use App\Domain\Person\Event\PersonRanksUpdated;
use App\Domain\PersonPayment\PersonPayment;
use App\Domain\PersonPrompt\PersonPrompt;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\Rank\Rank;
use App\Domain\Shared\AggregatedModel;
use App\Infrastructure\Laravel\Eloquent\Auth\ImpressionCast;
use Carbon\Carbon;
use Database\Factories\Domain\Person\PersonFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
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
 * @property Rank $current_rank
 * @property Carbon|null $current_rank_started_on
 * @property Carbon|null $current_rank_activated_on
 * @property Carbon|null $current_rank_finished_on
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
 *
 * @see PersonFactory
 */
#[Fillable(['lastname', 'firstname', 'birthday', 'club_id', 'from_base', 'created', 'updated', 'current_rank', 'current_rank_started_on', 'current_rank_activated_on', 'current_rank_finished_on'])]
#[Table(name: 'person')]
class Person extends AggregatedModel
{
    /** @see PersonFactory */
    use HasFactory;

    /** @var list<PersonRankHistory>|null */
    private ?array $rankHistoryToPersist = null;

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

    /** @return HasMany<PersonRankHistory, $this> */
    public function rankHistories(): HasMany
    {
        return $this->hasMany(PersonRankHistory::class);
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

    public function updateRanks(PersonRankState $rankState, Impression $impression): void
    {
        $this->current_rank = $rankState->current->rank;
        $this->current_rank_started_on = $rankState->current->startedOn;
        $this->current_rank_activated_on = $rankState->current->activatedOn;
        $this->current_rank_finished_on = $rankState->current->finishedOn;
        $this->updated = $impression;

        $this->rankHistoryToPersist = $rankState->history;

        $this->recordThat(new PersonRanksUpdated($this));
    }

    public function currentRank(): PersonRank
    {
        return new PersonRank(
            rank: $this->current_rank ?? Rank::WithoutRank,
            startedOn: $this->current_rank_started_on,
            activatedOn: $this->current_rank_activated_on,
            finishedOn: $this->current_rank_finished_on,
        );
    }

    /** @return list<PersonRankHistory>|null */
    public function rankHistoryToPersist(): ?array
    {
        return $this->rankHistoryToPersist;
    }

    protected function casts(): array
    {
        return [
            'prompt' => 'array',
            'citizenship' => Citizenship::class,
            'birthday' => 'datetime:Y-m-d',
            'current_rank' => Rank::class,
            'current_rank_started_on' => 'datetime:Y-m-d',
            'current_rank_activated_on' => 'datetime:Y-m-d',
            'current_rank_finished_on' => 'datetime:Y-m-d',
            'created' => ImpressionCast::class,
            'updated' => ImpressionCast::class,
        ];
    }
}
