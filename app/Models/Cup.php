<?php

namespace App\Models;

use App\Models\Cups\CupType;
use App\Models\Cups\CupTypeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

/**
 * Class Cup
 *
 * @package App\Models
 * @property int $id
 * @property string $name
 * @property int $events_count
 * @property int $year
 * @property string $type
 * @property-read Collection|CupEvent[] $events
 * @method static Builder|Cup find(mixed $ids)
 * @method static Builder|Cup with(mixed $ids)
 * @method static Builder|Cup where(string $column, int|string $value)
 */
class Cup extends Model
{
    public function events(): HasMany
    {
        return $this->hasMany(CupEvent::class);
    }

    public function getCupType(): CupTypeInterface
    {
        return CupType::getCupTypeClass($this->type);
    }
}
