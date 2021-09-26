<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Expression;
use Illuminate\Support\Collection;

/**
 * Class Group
 *
 * @package App\Models
 * @property int $id
 * @property string $name
 * @property Distance[] $distances
 * @property Cup[] $cups
 * @method static Group|null find(int $id)
 * @method static Builder|Group whereName(string $value)
 * @method static Builder|Group where(string $column, string $operator, string $value)
 * @method static Collection get()
 * @method static Group|null first()
 * @method static Builder|Group selectRaw(Expression $raw)
 * @method static Builder|Group join(string $table, string $tableId, string $operator, string $joinId)
 */
class Group extends Model
{
    public const MALE = 'male';
    public const FEMALE = 'female';

    public const MALE_GROUPS = [
        self::MALE => [
            'М10',
            'M10C',
            'М12',
            'М12Б',
            'М14',
            'М14_МТВО',
            'М14Б',
            'М16',
            'М16_МТВО',
            'М16Е',
            'М16А',
            'М16Б',
            'М18',
            'М18_МТВО',
            'М18Е',
            'М18А',
            'М20',
            'М21Е',
            'М21_МТВО',
            'М21А',
            'М21Б',
            'М21C',
            'М21D',
            'М35',
            'М40',
            'М45',
            'М50',
            'М55',
            'М60',
            'М65',
            'М70',
            'М75',
            'М80',
        ],
        self::FEMALE => [
            'Ж10',
            'Ж10C',
            'Ж12',
            'Ж12Б',
            'Ж14',
            'Ж14_МТВО',
            'Ж14Б',
            'Ж16',
            'Ж16Е',
            'Ж16_МТВО',
            'Ж16А',
            'Ж18',
            'Ж18_МТВО',
            'Ж18Е',
            'Ж18А',
            'Ж20',
            'Ж21Е',
            'Ж21_МТВО',
            'Ж21А',
            'Ж21Б',
            'Ж21C',
            'Ж21D',
            'Ж35',
            'Ж40',
            'Ж45',
            'Ж50',
            'Ж55',
            'Ж60',
            'Ж65',
            'Ж70',
            'Ж75',
            'Ж80',
        ],
    ];

    public const NEXT_GROUPS = [
        'М40' => 'М35',
        'М45' => 'М40',
        'М50' => 'М45',
        'М55' => 'М50',
        'М60' => 'М55',
        'М65' => 'М60',
        'М70' => 'М65',
        'М75' => 'М70',
        'М80' => 'М75',
        'Ж40' => 'Ж35',
        'Ж45' => 'Ж40',
        'Ж50' => 'Ж45',
        'Ж55' => 'Ж50',
        'Ж60' => 'Ж55',
        'Ж65' => 'Ж60' ,
        'Ж70' => 'Ж65',
        'Ж75' => 'Ж70',
        'Ж80' => 'Ж75',
    ];

    public const GROUPS = [
        'ММарк',
        'ЖМарк',
        'Ж1',
        'Ж2',
        'Ж3',
        'Ж4',
        'Ж5',
        'Ж6',
        'Ж7',
        'М1',
        'М2',
        'М3',
        'М4',
        'М5',
        'М6',
        'М7',
        'М10',
        'M10C',
        'М12',
        'М12Б',
        'М14',
        'М14_МТВО',
        'М14Б',
        'М16',
        'М16_МТВО',
        'М16Е',
        'М16А',
        'М16Б',
        'М18',
        'М18_МТВО',
        'М18Е',
        'М18А',
        'М20',
        'М21Е',
        'М21_МТВО',
        'М21А',
        'М21Б',
        'М21C',
        'М21D',
        'М35',
        'М40',
        'М45',
        'М50',
        'М55',
        'М60',
        'М65',
        'М70',
        'М75',
        'М80',
        'Ж10',
        'Ж10C',
        'Ж12',
        'Ж12Б',
        'Ж14',
        'Ж14_МТВО',
        'Ж14Б',
        'Ж16',
        'Ж16Е',
        'Ж16_МТВО',
        'Ж16А',
        'Ж18',
        'Ж18_МТВО',
        'Ж18Е',
        'Ж18А',
        'Ж20',
        'Ж21Е',
        'Ж21_МТВО',
        'Ж21А',
        'Ж21Б',
        'Ж21C',
        'Ж21D',
        'Ж35',
        'Ж40',
        'Ж45',
        'Ж50',
        'Ж55',
        'Ж60',
        'Ж65',
        'Ж70',
        'Ж75',
        'Ж80',
        'МЖ12',
        'МЖ14',
        'МЖ16',
        'МЖ18',
        'МЖ20',
        'МЖ21',
        'МЖ35',
        'МЖ40',
        'МЖ60',
        'OPEN1',
        'OPEN2',
        'OPEN3',
        'OPEN4',
    ];

    public const FIXING_MAP = [
        'M10' => 'М10',
        'W10' => 'Ж10',
        'W10C' => 'Ж10C',
        'W12' => 'Ж12',
        'Ж12В' => 'Ж12Б',
        'W14' => 'Ж14',
        'Ж14 МТВО' => 'Ж14_МТВО',
        'Ж18 МТВО' => 'Ж18_МТВО',
        'W20' => 'Ж20',
        'Ж16 МТВО' => 'Ж16_МТВО',
        'Ж14В' => 'Ж14Б',
        'W35' => 'Ж35',
        'W40' => 'Ж40',
        'W45' => 'Ж45',
        'W50' => 'Ж50',
        'W55' => 'Ж55',
        'W60' => 'Ж60',
        'W65' => 'Ж65',
        'W70' => 'Ж70',
        'W75' => 'Ж75',
        'W80' => 'Ж80',
        'Ж21' => 'Ж21Е',
        'M16A' => 'М16А',
        'M16E' => 'М16Е',
        'M18A' => 'М18А',
        'M18E' => 'М18Е',
        'W16A' => 'Ж16А',
        'W16E' => 'Ж16Е',
        'W16' => 'Ж16',
        'W18A' => 'Ж18А',
        'W18' => 'Ж18',
        'W21' => 'Ж21Е',
        'W18E' => 'Ж18Е',
        'ЖЕ' => 'Ж21Е',
        'ЖE' => 'Ж21Е',
        'Ж21 МТВО' => 'Ж21_МТВО',
        'Ж21A' => 'Ж21А',
        'ЖA' => 'Ж21А',
        'ЖА' => 'Ж21А',
        'W21A' => 'Ж21А',
        'Женщины группа А' => 'Ж21А',
        'Ж21E' => 'Ж21Е',
        'W21E' => 'Ж21Е',
        'Ж21 Фин Е' => 'Ж21Е',
        'Женщины группа Е' => 'Ж21Е',
        'Женщины группа В' => 'Ж21Б',
        'Ж21B' => 'Ж21Б',
        'W21B' => 'Ж21Б',
        'ЖB' => 'Ж21Б',
        'ЖВ' => 'Ж21Б',
        'М21A' => 'М21А',
        'M21A' => 'М21А',
        'М21 Фин А' => 'М21А',
        'МА' => 'М21А',
        'МA' => 'М21А',
        'Мужчины группа А' => 'М21А',
        'М21E' => 'М21Е',
        'М21 МТВО' => 'М21_МТВО',
        'МЕ' => 'М21Е',
        'Мужчины группа Е' => 'М21Е',
        'Мужчины группа В' => 'М21Б',
        'М21' => 'М21Е',
        'M21E' => 'М21Е',
        'МE' => 'М21Е',
        'М21 Фин Е' => 'М21Е',
        'M21' => 'М21Е',
        'МB' => 'М21Б',
        'M21B' => 'М21Б',
        'МВ' => 'М21Б',
        'М21B' => 'М21Б',
        'M12' => 'М12',
        'М12В' => 'М12Б',
        'M14' => 'М14',
        'М14 МТВО' => 'М14_МТВО',
        'М14В' => 'М14Б',
        'M16' => 'М16',
        'М16 МТВО' => 'М16_МТВО',
        'М16В' => 'М16Б',
        'M18' => 'М18',
        'М18 МТВО' => 'М18_МТВО',
        'M20' => 'М20',
        'M35' => 'М35',
        'M40' => 'М40',
        'M45' => 'М45',
        'M50' => 'М50',
        'M55' => 'М55',
        'M60' => 'М60',
        'M65' => 'М65',
        'M70' => 'М70',
        'M75' => 'М75',
        'M80' => 'М80',
        'Open' => 'OPEN1',
        'Оpen' => 'OPEN1',
        'Open1' => 'OPEN1',
        'Open 1' => 'OPEN1',
        'ОPEN' => 'OPEN1',
        'Оpen1' => 'OPEN1',
        'Open 2' => 'OPEN2',
        'Оpen2' => 'OPEN2',
        'Open2' => 'OPEN2',
        'Open 0' => 'OPEN1',
    ];

    public $timestamps = false;
    protected $table = 'groups';

    public function maleGroups(): Collection
    {
        if ($this->isMale()) {
            return new Collection(self::MALE_GROUPS[self::MALE]);
        } elseif ($this->isFeMale()) {
            return new Collection(self::MALE_GROUPS[self::FEMALE]);
        }
        return Collection::empty();
    }

    public function isMale(): bool
    {
        return mb_substr($this->name, 0, 1)  === 'М' || $this->name === 'M';
    }

    public function isFeMale(): bool
    {
        return mb_substr($this->name, 0, 1)  === 'Ж';
    }

    public function distances(): HasMany
    {
        return $this->hasMany(Distance::class, 'group_id', 'id');
    }

    public function caps(): BelongsToMany
    {
        return $this->belongsToMany(Cup::class, 'cup_groups');
    }

    public function years(): int
    {
        if (preg_match('#\d+#', $this->name, $match)) {
            return (int)$match[0];
        }
        return 0;
    }

    public function isPreviousGroup(int $groupId): bool
    {
        /** @var Group $group */
        $group = Group::find($groupId);
        return self::NEXT_GROUPS[$this->name] === $group->name;
    }
}
