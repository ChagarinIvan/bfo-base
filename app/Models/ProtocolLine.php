<?php

namespace App\Models;

use App\Services\IdentService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Class ProtocolLine
 *
 * @package App\Models
 * @property int $id
 * @property int $serial_number
 * @property string $lastname
 * @property string $firstname
 * @property string $club
 * @property int $year
 * @property string $rank
 * @property int $runner_number
 * @property null|Carbon $time
 * @property null|int $place
 * @property string $complete_rank
 * @property null|int $points
 * @property int $distance_id
 * @property int $person_id
 * @property string $prepared_line
 * @property bool $vk
 * @property-read Event $event
 * @property-read Distance $distance
 * @property-read Person|null $person
 * @method static Collection find(mixed $ids)
 * @method static ProtocolLine[]|Collection get(array $columns = ['*'])
 * @method static Builder|ProtocolLine whereDistanceId(int $distanceId)
 * @method static Builder|ProtocolLine wherePreparedLine(string $value)
 * @method static Builder|ProtocolLine wherePersonId(int $personId)
 * @method static Builder|ProtocolLine whereNotNull(string $column)
 * @method static Builder|ProtocolLine havingRaw(Expression $expression)
 * @method static Builder|ProtocolLine whereNull(string $column)
 * @method static Builder|ProtocolLine whereIn(string|Expression $column, array|Collection $list)
 * @method static Builder|ProtocolLine where(string|Expression $column, string|int $operator, int|string $value = '')
 * @method static Builder|ProtocolLine with(mixed $ids)
 * @method static Builder|ProtocolLine distinct()
 * @method static Builder|ProtocolLine orderByDesc(string $column)
 * @method static Builder|ProtocolLine selectRaw(Expression $raw)
 * @method static Builder|ProtocolLine addSelect(string $column)
 * @method static Builder|ProtocolLine join(string $table, string $tableId, string $operator, string $joinId)
 */
class ProtocolLine extends Model
{
    public $timestamps = false;
    protected $table = 'protocol_lines';

    protected $dates = ['time'];

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

    /**
     * Создаём идентификационную строку из фамилии имени и года
     * @return string
     */
    public function makeIdentLine(): string
    {
        $data = [
            IdentService::prepareLine(mb_strtolower($this->lastname)),
            IdentService::prepareLine(mb_strtolower($this->firstname)),
        ];
        if ($this->year !== null) {
            $data[] = $this->year;
        }
        return implode('_', $data);
    }

    public function fillProtocolLine(int $distanceId): void
    {
        $this->prepared_line = $this->makeIdentLine();

        //чистим разряды
        $this->rank = Rank::getRank($this->rank) ?? '';
        $this->complete_rank = Rank::getRank($this->complete_rank) ?? '';
        $this->distance_id = $distanceId;
    }
}
