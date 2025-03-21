<?php

declare(strict_types=1);

namespace App\Domain\Rank;

use App\Domain\Event\Event;
use App\Domain\Person\Person;
use App\Domain\Rank\Event\RankCreated;
use App\Domain\Shared\AggregatedModel;
use Carbon\Carbon;
use Database\Factories\Domain\Rank\RankFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use function array_flip;
use function array_key_exists;
use function array_map;
use function count;
use function in_array;
use function mb_strtolower;
use function str_replace;

/**
 * @property int $id
 * @property int $person_id
 * @property int|null $event_id
 * @property string $rank
 * @property Carbon $start_date
 * @property Carbon $finish_date
 * @property Carbon|null $activated_date
 *
 * @property-read Event|null $event
 * @property Person $person
 */
class Rank extends AggregatedModel
{
    /** @see RankFactory */
    use HasFactory;

    public const WSM_RANK = 'МСМК';
    public const SM_RANK = 'МС';
    public const SMC_RANK = 'КМС';
    public const FIRST_RANK = 'I';
    public const SECOND_RANK = 'II';
    public const THIRD_RANK = 'III';
    public const JUNIOR_FIRST_RANK = 'Iю';
    public const JUNIOR_SECOND_RANK = 'IIю';
    public const JUNIOR_THIRD_RANK = 'IIIю';
    public const WITHOUT_RANK = 'б/р';

    public const RANKS = [
        self::WSM_RANK => self::WSM_RANK,
        self::SM_RANK => self::SM_RANK,
        self::SMC_RANK => self::SMC_RANK,
        self::FIRST_RANK => self::FIRST_RANK,
        self::SECOND_RANK => self::SECOND_RANK,
        self::THIRD_RANK => self::THIRD_RANK,
        self::JUNIOR_FIRST_RANK => self::JUNIOR_FIRST_RANK,
        self::JUNIOR_SECOND_RANK => self::JUNIOR_SECOND_RANK,
        self::JUNIOR_THIRD_RANK => self::JUNIOR_THIRD_RANK,
        self::WITHOUT_RANK => self::WITHOUT_RANK,
    ];

    public const NEXT_RANKS = [
        self::SM_RANK => self::WSM_RANK,
        self::SMC_RANK => self::SM_RANK,
        self::FIRST_RANK => self::SMC_RANK,
        self::SECOND_RANK => self::FIRST_RANK,
        self::THIRD_RANK => self::SECOND_RANK,
    ];

    public const JUNIOR_RANKS = [
        self::JUNIOR_FIRST_RANK,
        self::JUNIOR_SECOND_RANK,
        self::JUNIOR_THIRD_RANK,
    ];

    public const MAX_JUNIOR_AGE = 18;

    private const PART_REPLACES = [
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

    private const FULL_REPLACES = [
        '1' => 'i',
        '2' => 'ii',
        '3' => 'iii',
    ];

    protected $fillable = ['finish_date', 'activated_date'];

    protected $table = 'ranks';
    protected $casts = [
        'start_date' => 'datetime:Y-m-d',
        'finish_date' => 'datetime:Y-m-d',
        'activated_date' => 'datetime:Y-m-d',
    ];

    private static array $preparedRanks = [];

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

    public static function autoActivation(string $rank): bool
    {
        return !in_array($rank, [self::SMC_RANK, self::SM_RANK, self::WSM_RANK], true);
    }

    private static function getPreparedRanks(): array
    {
        if (count(self::$preparedRanks) === 0) {
            self::$preparedRanks = array_map(static fn (string $rank): string => self::prepareRank($rank), self::RANKS);
        }
        return self::$preparedRanks;
    }

    private static function prepareRank(string $rank): string
    {
        $rank = mb_strtolower($rank);
        foreach (self::PART_REPLACES as $search => $replace) {
            $rank = str_replace($search, $replace, $rank);
        }
        foreach (self::FULL_REPLACES as $search => $replace) {
            if ($search == $rank) {
                $rank = $replace;
            }
        }
        return $rank;
    }

    public function event(): HasOne
    {
        return $this->hasOne(Event::class, 'id', 'event_id');
    }

    public function person(): HasOne
    {
        return $this->hasOne(Person::class, 'id', 'person_id');
    }

    public function create(): void
    {
//        dump('Create rank ' . $this->rank);
        $this->recordThat(new RankCreated($this));

        $this->save();
    }
}
