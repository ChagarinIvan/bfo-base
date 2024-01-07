<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\PersonsIdentService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Collection;

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
 *
 * @method static Collection find(mixed $ids)
 * @method static ProtocolLine[]|Collection get(array $columns = ['*'])
 * @method static Builder|ProtocolLine whereDistanceId(int $distanceId)
 * @method static Builder|ProtocolLine wherePreparedLine(string $value)
 * @method static Builder|ProtocolLine wherePersonId(int $personId)
 * @method static Builder|ProtocolLine whereNotNull(string $column)
 * @method static Builder|ProtocolLine havingRaw(Expression $expression)
 * @method static Builder|ProtocolLine whereNull(string $column)
 * @method static Builder|ProtocolLine whereIn(string|Expression $column, array|Collection $list)
 * @method static Builder|ProtocolLine where(string|Expression $column, string|int|bool $operator, int|string|Carbon $value = '')
 * @method static Builder|ProtocolLine orderBy(string $column)
 * @method Builder|ProtocolLine with(mixed $ids)
 * @method static Builder|ProtocolLine distinct()
 * @method static Builder|ProtocolLine orderByDesc(string $column)
 * @method static Builder|ProtocolLine selectRaw(Expression $raw)
 * @method Builder|ProtocolLine addSelect(string $column)
 * @method static Builder|ProtocolLine join(string $table, string $tableId, string $operator, string $joinId)
 * @method static ProtocolLine[] all()
 * @method static ProtocolLine[]|iterable cursor()
 */
class ProtocolLine extends Model
{
    public $timestamps = false;
    protected $table = 'protocol_lines';

    protected $dates = ['time', 'activate_rank'];

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
        $this->prepared_line = PersonsIdentService::makeIdentLine($this->lastname, $this->firstname, $this->year);

        //чистим разряды
        $this->rank = Rank::getRank($this->rank) ?? '';
        $this->complete_rank = Rank::getRank($this->complete_rank) ?? '';
        $this->distance_id = $distanceId;
    }
}
