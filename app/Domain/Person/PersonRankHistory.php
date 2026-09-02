<?php

declare(strict_types=1);

namespace App\Domain\Person;

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
class PersonRankHistory extends Model
{
    public static function fromValues(
        ?int $personId,
        int $protocolLineId,
        int $distanceId,
        int $eventId,
        int $competitionId,
        Rank $rank,
        RankChangeType $changeType,
        Carbon $achievedOn,
        ?Carbon $activatedOn,
        Carbon $startedOn,
        ?Carbon $finishedOn,
    ): self {
        $history = new self();
        $history->setRawAttributes([
            'person_id' => $personId,
            'protocol_line_id' => $protocolLineId,
            'distance_id' => $distanceId,
            'event_id' => $eventId,
            'competition_id' => $competitionId,
            'rank' => $rank->value,
            'change_type' => $changeType->value,
            'achieved_on' => $achievedOn,
            'activated_on' => $activatedOn,
            'started_on' => $startedOn,
            'finished_on' => $finishedOn,
        ], true);

        return $history;
    }

    public function protocolLine(): BelongsTo
    {
        return $this->belongsTo(ProtocolLine::class);
    }

    protected function casts(): array
    {
        return [
            'rank' => Rank::class,
            'change_type' => RankChangeType::class,
            'achieved_on' => 'datetime',
            'activated_on' => 'datetime',
            'started_on' => 'datetime',
            'finished_on' => 'datetime',
        ];
    }
}
