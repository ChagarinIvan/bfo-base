<?php

declare(strict_types=1);

namespace App\Infrastructure\Laravel\Eloquent\Person;

use App\Domain\Person\Person;
use App\Domain\Person\PersonRankHistory;
use App\Domain\Person\RankChangeType;
use App\Domain\ProtocolLine\ProtocolLine;
use App\Domain\Rank\Rank;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $person_id
 * @property int $protocol_line_id
 * @property int $distance_id
 * @property int $event_id
 * @property int $competition_id
 * @property Rank $rank
 * @property RankChangeType $change_type
 * @property Carbon $achieved_on
 * @property Carbon|null $activated_on
 * @property Carbon $started_on
 * @property Carbon|null $finished_on
 * @property-read ProtocolLine|null $protocolLine
 */

#[Fillable([
    'person_id', 'protocol_line_id', 'distance_id', 'event_id', 'competition_id',
    'rank', 'change_type', 'achieved_on', 'activated_on', 'started_on', 'finished_on',
])]
#[Table(name: 'person_rank_histories')]
#[WithoutTimestamps]
final class PersonRankHistoryRecord extends Model
{
    public function protocolLine(): BelongsTo
    {
        return $this->belongsTo(ProtocolLine::class);
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    public function toDomain(): PersonRankHistory
    {
        return new PersonRankHistory(
            protocolLineId: $this->protocol_line_id,
            distanceId: $this->distance_id,
            eventId: $this->event_id,
            competitionId: $this->competition_id,
            rank: $this->rank,
            changeType: $this->change_type,
            achievedOn: $this->achieved_on,
            activatedOn: $this->activated_on,
            startedOn: $this->started_on,
            finishedOn: $this->finished_on,
        );
    }

    protected function casts(): array
    {
        return [
            'rank' => Rank::class,
            'change_type' => RankChangeType::class,
            'achieved_on' => 'datetime:Y-m-d',
            'activated_on' => 'datetime:Y-m-d',
            'started_on' => 'datetime:Y-m-d',
            'finished_on' => 'datetime:Y-m-d',
        ];
    }
}
