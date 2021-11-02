<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Class Rank
 *
 * @package App\Models
 * @property int $id
 * @property int $person_id
 * @property int|null $event_id
 * @property null|string $rank
 * @property Carbon $start_date
 * @property Carbon $finish_date
 * @property-read Event|null $event
 * @property-read Person $person
 * @method static Rank|Builder where(string $column, string|null $operator, string|int|Carbon $value = null)
 * @method static Rank|Builder selectRaw(Expression $expression)
 * @method static Rank|Builder whereIn(string $column, array|Collection $list)
 * @method static Rank|Builder with(array|string $relations)
 * @method static Rank|Builder orderByRaw(Expression $expression)
 * @method static Rank|Builder join(string $table, string $foreignColumn, string $operator, string $selfColumn)
 * @method static Rank[]|Collection get()
 */
class Rank extends Model
{
    public const WSM_RANK = 'МСМК';
    public const SM_RANK = 'МС';
    public const SMC_RANK = 'КМС';
    public const FIRST_RANK = 'I';
    public const SECOND_RANK = 'II';
    public const THIRD_RANK = 'III';
    public const UNIOR_FIRST_RANK = 'Iю';
    public const UNIOR_SECOND_RANK = 'IIю';
    public const UNIOR_THIRD_RANK = 'IIIю';
    public const WITHOUT_RANK = 'б/р';

    public const RANKS = [
        self::WSM_RANK => self::WSM_RANK,
        self::SM_RANK => self::SM_RANK,
        self::SMC_RANK => self::SMC_RANK,
        self::FIRST_RANK => self::FIRST_RANK,
        self::SECOND_RANK => self::SECOND_RANK,
        self::THIRD_RANK => self::THIRD_RANK,
        self::UNIOR_FIRST_RANK => self::UNIOR_FIRST_RANK,
        self::UNIOR_SECOND_RANK => self::UNIOR_SECOND_RANK,
        self::UNIOR_THIRD_RANK => self::UNIOR_THIRD_RANK,
        self::WITHOUT_RANK => self::WITHOUT_RANK,
    ];

    public const PREVIOUS_RANKS = [
        self::WSM_RANK => self::SM_RANK,
        self::SM_RANK => self::SMC_RANK,
        self::SMC_RANK => self::FIRST_RANK,
        self::FIRST_RANK => self::SECOND_RANK,
        self::SECOND_RANK => self::THIRD_RANK,
    ];

    public const NEXT_RANKS = [
        self::SM_RANK => self::WSM_RANK,
        self::SMC_RANK => self::SM_RANK,
        self::FIRST_RANK => self::SMC_RANK,
        self::SECOND_RANK => self::FIRST_RANK,
        self::THIRD_RANK => self::SECOND_RANK,
    ];

    private const REPLACES = [
        'к' => 'к',
        '1ю' => 'iю',
        '2ю' => 'iiю',
        '3ю' => 'iiiю',
        '1р' => 'i',
        '2р' => 'ii',
        '3р' => 'iii',
        'k' => 'к',
        'm' => 'м',
        'c' => 'с',
        '/' => '',
        '\\' => '',
    ];

    public $timestamps = false;
    protected $table = 'ranks';
    protected $dates = ['start_date', 'finish_date'];

    private static array $preparedRanks = [];

    public function event(): HasOne
    {
        return $this->hasOne(Event::class, 'id', 'event_id');
    }

    public function person(): HasOne
    {
        return $this->hasOne(Person::class, 'id', 'person_id');
    }

    public static function validateRank(string $rank): bool
    {
        return in_array(self::prepareRank($rank), self::getPreparedRanks(), true);
    }

    public static function getRank(?string $rank): ?string
    {
        if ($rank === null) {
            return null;
        }
        $ranks = array_flip(self::getPreparedRanks());
        $rank = self::prepareRank($rank);
        if (array_key_exists($rank, $ranks)) {
            return $ranks[$rank];
        }
        return null;
    }

    private static function getPreparedRanks(): array
    {
        if (count(self::$preparedRanks) === 0) {
            self::$preparedRanks = array_map(fn(string $rank) => self::prepareRank($rank), self::RANKS);
        }
        return self::$preparedRanks;
    }

    private static function prepareRank(string $rank): string
    {
        $rank = mb_strtolower($rank);
        foreach (self::REPLACES as $search => $replace) {
            $rank = str_replace((string)$search, $replace, $rank);
        }
        return $rank;
    }
}
