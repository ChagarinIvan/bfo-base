<?php

declare(strict_types=1);

namespace App\Domain\ProtocolLine;

use App\Domain\Distance\Distance;
use App\Domain\Event\Event;
use App\Domain\Person\Person;
use App\Domain\ProtocolLine\Event\ProtocolLineRankActivated;
use App\Domain\Rank\Rank;
use App\Domain\Shared\AggregatedModel;
use App\Services\PersonsIdentService;
use Carbon\Carbon;
use Database\Factories\Domain\ProtocolLine\ProtocolLineFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $serial_number
 * @property string $lastname
 * @property string $firstname
 * @property string $club
 * @property int|null $year
 * @property string $rank
 * @property int $runner_number
 * @property null|Carbon $time
 * @property null|int $place
 * @property string $complete_rank
 * @property null|int $points
 * @property int $distance_id
 * @property null|int $person_id
 * @property string $prepared_line
 * @property bool $vk
 * @property null|Carbon $activate_rank
 *
 * @property-read Event $event
 * @property-read Distance $distance
 * @property-read Person|null $person
 */
class ProtocolLine extends AggregatedModel
{
    /** @see ProtocolLineFactory */
    use HasFactory;

    public $timestamps = false;
    protected $table = 'protocol_lines';

    protected $casts = [
        'time' => 'datetime',
        'activate_rank' => 'datetime:Y-m-d',
    ];

    protected $fillable = [
        'serial_number',
        'lastname',
        'firstname',
        'club',
        'year',
        'rank',
        'runner_number',
        'time',
        'place',
        'complete_rank',
        'points',
        'vk',
        'distance_id',
        'prepared_line',
        'person_id',
        'activate_rank',
    ];

//    public function getDateFormat(): string
//    {
//        return 'H:i:s';
//    }

    public function distance(): BelongsTo
    {
        return $this->belongsTo(Distance::class, 'distance_id', 'id');
    }

    public function event(): BelongsTo
    {
        return $this->distance->event();
    }

    public function person(): BelongsTo
    {
        return $this->BelongsTo(Person::class, 'person_id', 'id');
    }

    public function fillProtocolLine(int $distanceId): void
    {
        $this->prepared_line = PersonsIdentService::makeIdentLine($this->lastname, $this->firstname, $this->year ? (int)$this->year : null);

        //чистим разряды
        $this->rank = Rank::getRank($this->rank) ?? '';
        $this->complete_rank = Rank::getRank($this->complete_rank) ?? '';
        $this->distance_id = $distanceId;
    }

    public function activateRank(?Carbon $date): void
    {
        $this->activate_rank = $date;

        $this->recordThat(new ProtocolLineRankActivated($this));
    }
}
