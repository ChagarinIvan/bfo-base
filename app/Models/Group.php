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
 * @property Distance[]|Collection $distances
 * @property Cup[] $cups
 * @method static Group|null find(int $id)
 * @method static Builder|Group whereName(string $value)
 * @method static Builder|Group where(string $column, string $operator, string $value)
 * @method static Collection get()
 * @method static Collection all()
 * @method static Group|null first()
 * @method static Builder|Group with(mixed $params)
 * @method static Builder|Group selectRaw(Expression $raw)
 * @method static Builder|Group join(string $table, string $tableId, string $operator, string $joinId)
 */
class Group extends Model
{
    public const MALE = 'male';
    public const FEMALE = 'female';

    public const M21E = 'М21Е';
    public const W21E = 'Ж21Е';

    public const M21_MTBO = 'М21_МТВО';
    public const W21_MTBO = 'Ж21_МТВО';

    public const M35 = 'М35';
    public const M40 = 'М40';
    public const M45 = 'М45';
    public const M50 = 'М50';
    public const M55 = 'М55';
    public const M60 = 'М60';
    public const M65 = 'М65';
    public const M70 = 'М70';
    public const M75 = 'М75';
    public const M80 = 'М80';

    public const W35 = 'Ж35';
    public const W40 = 'Ж40';
    public const W45 = 'Ж45';
    public const W50 = 'Ж50';
    public const W55 = 'Ж55';
    public const W60 = 'Ж60';
    public const W65 = 'Ж65';
    public const W70 = 'Ж70';
    public const W75 = 'Ж75';
    public const W80 = 'Ж80';

    public const M20 = 'М20';
    public const W20 = 'Ж20';

    public const M21A = 'М21А';
    public const M21B = 'М21Б';
    public const W21A = 'Ж21А';
    public const W21B = 'Ж21Б';

    public const M18 = 'М18';
    public const M16 = 'М16';
    public const M14 = 'М14';
    public const M12 = 'М12';

    public const W18 = 'Ж18';
    public const W16 = 'Ж16';
    public const W14 = 'Ж14';
    public const W12 = 'Ж12';

    public const MALE_GROUPS = [
        self::MALE => [
            'М10',
            'M10C',
            self::M12,
            'М12Б',
            self::M14,
            'М14_МТВО',
            'М14Б',
            self::M16,
            'М16_МТВО',
            'М16Е',
            'М16А',
            'М16Б',
            self::M18,
            'М18_МТВО',
            'М18Е',
            'М18А',
            self::M20,
            self::M21E,
            self::M21_MTBO,
            self::M21A,
            self::M21B,
            'М21C',
            'М21D',
            self::M35,
            self::M40,
            self::M45,
            self::M50,
            self::M55,
            self::M60,
            self::M65,
            self::M70,
            self::M75,
            self::M80,
        ],
        self::FEMALE => [
            'Ж10',
            'Ж10C',
            self::W12,
            'Ж12Б',
            self::W14,
            'Ж14_МТВО',
            'Ж14Б',
            self::W16,
            'Ж16Е',
            'Ж16_МТВО',
            'Ж16А',
            self::W18,
            'Ж18_МТВО',
            'Ж18Е',
            'Ж18А',
            self::W20,
            self::W21E,
            self::W21_MTBO,
            self::W21A,
            self::W21B,
            'Ж21C',
            'Ж21D',
            self::W35,
            self::W40,
            self::W45,
            self::W50,
            self::W55,
            self::W60,
            self::W65,
            self::W70,
            self::W75,
            self::W80,
        ],
    ];

    public const FIXING_MAP = [
        'M10' => 'М10',
        'W10' => 'Ж10',
        'W10C' => 'Ж10C',
        'W12' => self::W12,
        'Ж12В' => 'Ж12Б',
        'W14' => self::W14,
        'Ж14 МТВО' => 'Ж14_МТВО',
        'Ж18 МТВО' => 'Ж18_МТВО',
        'W20' => self::W20,
        'Ж16 МТВО' => 'Ж16_МТВО',
        'Ж14В' => 'Ж14Б',
        'W35' => self::W35,
        'W40' => self::W40,
        'W45' => self::W45,
        'W50' => self::W50,
        'W55' => self::W55,
        'W60' => self::W60,
        'W65' => self::W65,
        'W70' => self::W70,
        'W75' => self::W75,
        'W80' => self::W80,
        'Ж21' => self::W21E,
        'M16A' => 'М16А',
        'M16E' => 'М16Е',
        'M18A' => 'М18А',
        'M18E' => 'М18Е',
        'W16A' => 'Ж16А',
        'W16E' => 'Ж16Е',
        'W16' => self::W16,
        'W18A' => 'Ж18А',
        'W18' => self::W18,
        'W21' => self::W21E,
        'W18E' => 'Ж18Е',
        'ЖЕ' => self::W21E,
        'ЖE' => self::W21E,
        'Ж21 МТВО' => self::W21_MTBO,
        'Ж21A' => self::W21A,
        'ЖA' => self::W21A,
        'ЖА' => self::W21A,
        'W21A' => self::W21A,
        'Женщины группа А' => self::W21A,
        'Ж21E' => self::W21E,
        'W21E' => self::W21E,
        'Ж21 Фин Е' => self::W21E,
        'Женщины группа Е' => self::W21E,
        'Женщины группа В' => self::W21B,
        'Ж21B' => self::W21B,
        'W21B' => self::W21B,
        'ЖB' => self::W21B,
        'ЖВ' => self::W21B,
        'М21A' => self::M21A,
        'M21A' => self::M21A,
        'М21 Фин А' => self::M21A,
        'МА' => self::M21A,
        'МA' => self::M21A,
        'Мужчины группа А' => self::M21A,
        'М21E' => self::M21E,
        'М21 МТВО' => self::M21_MTBO,
        'МЕ' => self::M21E,
        'Мужчины группа Е' => self::M21E,
        'Мужчины группа В' => self::M21B,
        'М21' => self::M21E,
        'M21E' => self::M21E,
        'МE' => self::M21E,
        'М21 Фин Е' => self::M21E,
        'M21' => self::M21E,
        'МB' => self::M21B,
        'M21B' => self::M21B,
        'МВ' => self::M21B,
        'М21B' => self::M21B,
        'M12' => self::M12,
        'М12В' => 'М12Б',
        'M14' => self::M14,
        'М14 МТВО' => 'М14_МТВО',
        'М14В' => 'М14Б',
        'M16' => self::M16,
        'М16 МТВО' => 'М16_МТВО',
        'М16В' => 'М16Б',
        'M18' => self::M18,
        'М18 МТВО' => 'М18_МТВО',
        'M20' => self::M20,
        'M35' => self::M35,
        'M40' => self::M40,
        'M45' => self::M45,
        'M50' => self::M50,
        'M55' => self::M55,
        'M60' => self::M60,
        'M65' => self::M65,
        'M70' => self::M70,
        'M75' => self::M75,
        'M80' => self::M80,
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
}
